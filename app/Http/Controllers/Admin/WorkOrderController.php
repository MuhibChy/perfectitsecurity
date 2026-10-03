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
        $schedules = \App\Models\OrderPaymentSchedule::where('order_id', $order->id)->orderBy('sort_order')->get();
        $minDeposit = round((float) $order->total * (ServiceOrderWorkflowService::MIN_DEPOSIT_PERCENTAGE / 100), 2);
        // Closure readiness (same rules as closeOrder): incomplete tasks + outstanding due.
        $openTasksCount = $order->tasks()->where('status', '!=', 'completed')->count();
        $canClose = ($order->task_completed_at || $openTasksCount === 0) && (float) $order->amount_due <= 0;

        return view('admin.work-orders.show', compact('order', 'employees', 'minDeposit', 'schedules', 'openTasksCount', 'canClose'));
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
            'schedule_id' => ['nullable', 'exists:order_payment_schedules,id'],
        ]);

        $result = $this->workflowService->recordPayment($order, $request->all(), auth()->user());
        $payment = $result['payment'];

        // Cash memo: document header for cash payments, same transaction.
        if (strtolower((string) $request->payment_method) === 'cash') {
            \App\Models\CashMemo::firstOrCreate(
                ['payment_id' => $payment->id],
                ['issued_by' => auth()->id(), 'issued_at' => now()]
            );
        }

        // Apply to a payment schedule row when selected. Serialized under a
        // row lock so concurrent payments cannot double-count paid_amount.
        if ($request->filled('schedule_id')) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $order, $payment) {
                $schedule = \App\Models\OrderPaymentSchedule::where('id', $request->schedule_id)->where('order_id', $order->id)->lockForUpdate()->first();
                if ($schedule && $schedule->status !== 'waived') {
                    $schedule->paid_amount = round((float) $schedule->paid_amount + (float) $payment->amount, 2);
                    $schedule->status = $schedule->paid_amount >= (float) $schedule->expected_amount ? 'paid' : 'partial';
                    $schedule->save();
                    $payment->update(['schedule_id' => $schedule->id]);
                }
            });
        }

        \App\Services\ServiceTrackingService::record([
            'entity_type' => \App\Models\ServiceOrder::class, 'entity_id' => $order->id,
            'order_id' => $order->id, 'customer_id' => $order->customer_id,
            'action' => 'progress_updated', 'comment' => "Payment {$payment->payment_number} ({$payment->payment_method}) recorded.",
            'visible' => true,
        ]);

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

    /** Define a planned stage payment. Sum of active rows may not exceed the order total. */
    public function storeSchedule(Request $request, $id)
    {
        $order = ServiceOrder::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'milestone_id' => 'nullable|exists:project_milestones,id',
            'expected_amount' => 'required|numeric|min:0.01',
            'due_at' => 'nullable|date',
        ]);
        $planned = (float) \App\Models\OrderPaymentSchedule::where('order_id', $order->id)->where('status', '!=', 'waived')->sum('expected_amount');
        if (round($planned + (float) $data['expected_amount'], 2) > round((float) $order->total, 2)) {
            return back()->with('error', 'Payment schedule exceeds the order total. Record an approved scope change first.');
        }
        \App\Models\OrderPaymentSchedule::create($data + [
            'order_id' => $order->id, 'created_by' => auth()->id(),
            'sort_order' => \App\Models\OrderPaymentSchedule::where('order_id', $order->id)->count(),
        ]);
        \App\Services\ServiceTrackingService::record([
            'entity_type' => \App\Models\ServiceOrder::class, 'entity_id' => $order->id,
            'order_id' => $order->id, 'customer_id' => $order->customer_id,
            'action' => 'progress_updated', 'comment' => "Payment schedule '{$data['title']}' planned.",
            'visible' => false,
        ]);

        return back()->with('success', 'Stage payment scheduled.');
    }

    public function rejectPrice(Request $request, $id)
    {
        $order = ServiceOrder::findOrFail($id);
        $data = $request->validate(['reason' => 'required|string|max:1000', 'revision_id' => 'nullable|integer']);
        $this->workflowService->rejectPrice($order, auth()->user(), $data['revision_id'] ?? null, $data['reason']);

        return back()->with('success', 'Price proposal rejected.');
    }

    public function cancel(Request $request, $id)
    {
        $order = ServiceOrder::findOrFail($id);
        $data = $request->validate(['reason' => 'required|string|max:1000']);
        $this->workflowService->cancelOrder($order, auth()->user(), $data['reason']);

        return back()->with('success', "Service Order {$order->order_number} cancelled; history preserved.");
    }

    public function close(Request $request, $id)
    {
        $order = ServiceOrder::findOrFail($id);
        $data = $request->validate(['closure_notes' => 'nullable|string|max:2000']);
        $this->workflowService->closeOrder($order, auth()->user(), $data['closure_notes'] ?? null);
        \App\Services\ServiceTrackingService::record([
            'entity_type' => ServiceOrder::class, 'entity_id' => $order->id,
            'order_id' => $order->id, 'customer_id' => $order->customer_id,
            'action' => 'completed', 'comment' => $data['closure_notes'] ?? null,
            'reason' => 'Order closed', 'visible' => true,
        ]);

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
