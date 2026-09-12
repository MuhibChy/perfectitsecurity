<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function __construct(private ServiceOrderWorkflowService $workflowService)
    {
    }

    public function index(Request $request)
    {
        $query = ServiceOrder::with(['customer', 'service', 'assignee', 'invoices', 'receipts', 'tasks'])
            ->latest();

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_auth')) {
            $query->where('payment_authorization', $request->payment_auth);
        }

        if ($request->filled('search')) {
            $s = addcslashes($request->search, '%_\\');
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', "%{$s}%")
                    ->orWhereHas('customer', function ($cq) use ($s) {
                        $cq->where('name', 'like', "%{$s}%")
                            ->orWhere('email', 'like', "%{$s}%")
                            ->orWhere('phone', 'like', "%{$s}%");
                    })
                    ->orWhereHas('service', function ($sq) use ($s) {
                        $sq->where('name', 'like', "%{$s}%");
                    });
            });
        }

        $orders = $query->paginate(20);

        // Management Summary Metrics
        $totalOrders = ServiceOrder::count();
        $totalRevenue = ServiceOrder::sum('amount_paid');
        $totalOutstanding = ServiceOrder::sum('amount_due');
        $readyToStart = ServiceOrder::where('payment_authorization', 'ready_to_start')->count();
        $awaitingPayment = ServiceOrder::whereIn('status', ['awaiting_payment', 'awaiting_final_payment'])->count();

        return view('admin.work-orders.index', compact(
            'orders', 'totalOrders', 'totalRevenue', 'totalOutstanding', 'readyToStart', 'awaitingPayment'
        ));
    }

    public function create()
    {
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $employees = User::staff()->orderBy('name')->get();
        $customers = User::customers()->orderBy('name')->limit(50)->get();

        return view('admin.work-orders.create', compact('services', 'employees', 'customers'));
    }

    public function searchCustomers(Request $request)
    {
        $q = addcslashes($request->get('q', ''), '%_\\');
        $rawId = $request->get('q', '');
        $customers = User::customers()
            ->where(function ($query) use ($q, $rawId) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
                if (ctype_digit((string) $rawId)) {
                    $query->orWhere('id', (int) $rawId);
                }
            })
            ->limit(20)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'email' => $c->email,
                    'phone' => $c->phone,
                    'is_fully_verified' => $c->isFullyVerified(),
                    'is_email_verified' => $c->isEmailVerified(),
                    'is_phone_verified' => $c->isPhoneVerified(),
                    'verification_status' => $c->verification_status,
                ];
            });

        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $rules = [
            'service_id' => ['required', 'exists:services,id'],
            'requirements' => ['required', 'string', 'min:10'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'currency' => ['nullable', 'string', 'size:3'],
            'priority' => ['nullable', 'in:low,medium,high,critical'],
            'urgency' => ['nullable', 'in:low,medium,high,critical'],
            'order_source_label' => ['nullable', 'string', 'max:100'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'preferred_date' => ['nullable', 'date'],
            'customer_notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
        ];

        if ($request->customer_mode === 'existing') {
            $rules['customer_id'] = ['required', 'exists:users,id'];
        } else {
            $rules['customer_name'] = ['required', 'string', 'max:255'];
            $rules['customer_email'] = ['required', 'email', 'max:255'];
            $rules['customer_phone'] = ['nullable', 'string', 'max:30'];
        }

        $request->validate($rules);

        $order = $this->workflowService->createManualWorkOrder($request->all(), auth()->user());

        return redirect()->route('admin.work-orders.show', $order->id)
            ->with('success', "Manual Work Order {$order->order_number} created successfully.");
    }

    public function show($id)
    {
        $order = ServiceOrder::with([
            'customer', 'service.category', 'creator', 'assignee', 'managerOverride',
            'priceRevisions.proposer', 'invoices.items', 'payments.receipt',
            'receipts.payment', 'tickets.assignee', 'tasks.assignee',
            'expenses.worker',
        ])->findOrFail($id);

        $employees = User::staff()->orderBy('name')->get();
        $minDeposit = round((float) $order->total * (ServiceOrderWorkflowService::MIN_DEPOSIT_PERCENTAGE / 100), 2);

        return view('admin.work-orders.show', compact('order', 'employees', 'minDeposit'));
    }

    public function proposePrice(Request $request, $id)
    {
        $order = ServiceOrder::findOrFail($id);
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'kind' => ['nullable', 'in:employee_offer,discount,final_offer'],
            'terms' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->workflowService->proposePrice($order, $request->all(), auth()->user());

        return back()->with('success', 'Price offer updated successfully.');
    }

    public function approvePrice(Request $request, $id)
    {
        $order = ServiceOrder::findOrFail($id);
        $this->workflowService->acceptPrice($order, auth()->user(), $request->revision_id ? (int) $request->revision_id : null);

        return back()->with('success', 'Final price approved and order records activated.');
    }

    public function recordPayment(Request $request, $id)
    {
        $order = ServiceOrder::findOrFail($id);
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $result = $this->workflowService->recordPayment($order, $request->all(), auth()->user());

        return back()->with('success', "Payment of {$order->currency} {$request->amount} recorded. Receipt {$result['receipt']->receipt_number} issued.");
    }

    public function managerOverride(Request $request, $id)
    {
        $order = ServiceOrder::findOrFail($id);
        $request->validate([
            'override_reason' => ['required', 'string', 'min:5'],
        ]);

        $this->workflowService->managerOverride($order, auth()->user(), $request->override_reason);

        return back()->with('success', 'Manager override applied. Work is now authorized to start.');
    }

    public function addExpense(Request $request, $id)
    {
        $order = ServiceOrder::findOrFail($id);
        $request->validate([
            'cost_type' => ['required', 'in:labour,freelancer,commission,software,cloud,vendor,travel,other'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'hours' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'commission_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'worker_id' => ['nullable', 'exists:users,id'],
            'description' => ['required', 'string', 'max:255'],
            'vendor' => ['nullable', 'string', 'max:255'],
        ]);

        $expense = $this->workflowService->recordExpense($order, $request->all(), auth()->user());

        return back()->with('success', "Expense {$expense->expense_number} recorded successfully.");
    }

    public function close(Request $request, $id)
    {
        $order = ServiceOrder::findOrFail($id);
        $this->workflowService->closeOrder($order, auth()->user(), $request->closure_notes);

        return back()->with('success', "Service Order {$order->order_number} has been officially closed.");
    }

    public function showReceipt($id, $receiptId)
    {
        $order = ServiceOrder::findOrFail($id);
        $receipt = Receipt::with(['customer', 'invoice', 'payment', 'order.service'])
            ->where('service_order_id', $order->id)
            ->findOrFail($receiptId);
        abort_unless(
            (int) $receipt->service_order_id === (int) $order->id
            && (int) $receipt->customer_id === (int) $order->customer_id,
            404
        );

        return view('admin.receipts.show', compact('receipt'));
    }
}
