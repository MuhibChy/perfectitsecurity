<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function __construct(private ServiceOrderWorkflowService $workflowService)
    {
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $orders = ServiceOrder::where('customer_id', $user->id)
            ->with(['service', 'invoices', 'tickets', 'tasks', 'receipts'])
            ->latest()
            ->paginate(15);

        return view('customer.orders.index', compact('orders'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $selectedService = $request->service_id ? Service::find($request->service_id) : null;
        $discussPrice = (bool) $request->get('discuss', 0);

        return view('customer.orders.create', compact('services', 'selectedService', 'discussPrice', 'user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id,is_active,1'],
            'requirements' => ['required', 'string', 'min:10'],
            'urgency' => ['nullable', 'in:low,medium,high,critical'],
            'preferred_date' => ['nullable', 'date'],
            'customer_notes' => ['nullable', 'string'],
            'proposed_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user = auth()->user();
        // Whitelist only customer-supplied fields — never pass customer_id,
        // final_price, totals, payment status or price_locked from the request.
        $order = $this->workflowService->createCustomerOrder($request->only([
            'service_id', 'requirements', 'urgency', 'preferred_date',
            'customer_notes', 'proposed_price',
        ]), $user);

        $msg = $order->status === 'negotiating'
            ? 'Service price discussion initiated. Our team will review your requirements.'
            : 'Service order created successfully!';

        return redirect()->route('portal.orders.show', $order->id)->with('success', $msg);
    }

    public function show($id)
    {
        $user = auth()->user();
        // Owner-scoped lookup: another customer's order is indistinguishable
        // from a nonexistent one (404, never 403 — no existence leak).
        $order = ServiceOrder::where('customer_id', $user->id)->with([
            'service', 'priceRevisions.proposer', 'invoices.items', 'payments.receipt',
            'receipts.invoice', 'tickets', 'tasks',
        ])->findOrFail($id);

        $minDeposit = round((float) $order->total * (ServiceOrderWorkflowService::MIN_DEPOSIT_PERCENTAGE / 100), 2);

        return view('customer.orders.show', compact('order', 'minDeposit'));
    }

    public function negotiate(Request $request, $id)
    {
        $user = auth()->user();
        $order = ServiceOrder::where('customer_id', $user->id)->findOrFail($id);

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'terms' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->workflowService->proposePrice($order, [
            'amount' => $request->amount,
            'terms' => $request->terms,
            'kind' => 'customer_proposal',
        ], $user);

        return back()->with('success', 'Your counter-offer has been submitted.');
    }

    public function acceptPrice(Request $request, $id)
    {
        $user = auth()->user();
        $order = ServiceOrder::where('customer_id', $user->id)->findOrFail($id);

        $this->workflowService->acceptPrice($order, $user, $request->revision_id ? (int) $request->revision_id : null);

        return back()->with('success', 'Price accepted! Your order is now confirmed and invoice generated.');
    }

    public function rejectPrice(Request $request, $id)
    {
        $user = auth()->user();
        $order = ServiceOrder::where('customer_id', $user->id)->findOrFail($id);
        $data = $request->validate(['reason' => 'required|string|max:1000', 'revision_id' => 'nullable|integer']);
        $this->workflowService->rejectPrice($order, $user, $data['revision_id'] ?? null, $data['reason']);

        return back()->with('success', 'Price proposal declined.');
    }

    public function cancel(Request $request, $id)
    {
        $user = auth()->user();
        $order = ServiceOrder::where('customer_id', $user->id)->findOrFail($id);
        $data = $request->validate(['reason' => 'required|string|max:1000']);
        $this->workflowService->cancelOrder($order, $user, $data['reason']);

        return back()->with('success', 'Order cancelled. History is preserved.');
    }

    public function pay(Request $request, $id)
    {
        $user = auth()->user();
        $order = ServiceOrder::where('customer_id', $user->id)->findOrFail($id);

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['nullable', 'string'],
        ]);

        $result = $this->workflowService->recordPayment($order, [
            'amount' => $request->amount,
            'payment_method' => $request->payment_method ?? 'credit_card',
            'transaction_id' => 'TXN-'.strtoupper(\Illuminate\Support\Str::random(10)),
        ], $user);

        return back()->with('success', "Payment of {$order->currency} {$request->amount} recorded! Receipt {$result['receipt']->receipt_number} issued.");
    }

    public function showReceipt($orderId, $receiptId)
    {
        $user = auth()->user();
        $order = ServiceOrder::where('customer_id', $user->id)->findOrFail($orderId);

        $receipt = Receipt::with(['customer', 'invoice', 'payment', 'order.service'])
            ->where('service_order_id', $order->id)
            ->findOrFail($receiptId);

        return view('admin.receipts.show', compact('receipt'));
    }

    /** Customer confirms the completed service (recorded, never silent). */
    public function confirmCompletion($id)
    {
        $user = auth()->user();
        $order = ServiceOrder::where('customer_id', $user->id)->findOrFail($id);
        abort_unless($order->task_completed_at || ! $order->tasks()->where('status', '!=', 'completed')->exists(), 422, 'Service work is not yet complete.');

        \App\Services\ServiceTrackingService::record([
            'entity_type' => ServiceOrder::class, 'entity_id' => $order->id,
            'order_id' => $order->id, 'customer_id' => $user->id,
            'action' => 'completed', 'comment' => 'Customer confirmed completion.',
            'visible' => true,
        ]);
        if ($order->assigned_to ?? $order->created_by) {
            \App\Services\ServiceTrackingService::notify($order->assigned_to ?? $order->created_by, 'customer_confirmation', 'Customer confirmed completion', "Customer confirmed {$order->order_number}.");
        }

        return back()->with('success', 'Thank you — your confirmation has been recorded in the service history.');
    }
}
