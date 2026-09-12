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
            'service_id' => ['required', 'exists:services,id'],
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
        $order = ServiceOrder::with([
            'service', 'priceRevisions.proposer', 'invoices.items', 'payments.receipt',
            'receipts.invoice', 'tickets', 'tasks',
        ])->findOrFail($id);

        // Strict customer isolation / IDOR protection
        abort_unless($order->customer_id === $user->id, 403, 'Unauthorized access to this order.');

        $minDeposit = round((float) $order->total * (ServiceOrderWorkflowService::MIN_DEPOSIT_PERCENTAGE / 100), 2);

        return view('customer.orders.show', compact('order', 'minDeposit'));
    }

    public function negotiate(Request $request, $id)
    {
        $user = auth()->user();
        $order = ServiceOrder::findOrFail($id);
        abort_unless($order->customer_id === $user->id, 403);

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
        $order = ServiceOrder::findOrFail($id);
        abort_unless($order->customer_id === $user->id, 403);

        $this->workflowService->acceptPrice($order, $user, $request->revision_id ? (int) $request->revision_id : null);

        return back()->with('success', 'Price accepted! Your order is now confirmed and invoice generated.');
    }

    public function pay(Request $request, $id)
    {
        $user = auth()->user();
        $order = ServiceOrder::findOrFail($id);
        abort_unless($order->customer_id === $user->id, 403);

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['nullable', 'string'],
        ]);

        $result = $this->workflowService->recordPayment($order, [
            'amount' => $request->amount,
            'payment_method' => $request->payment_method ?? 'credit_card',
            'transaction_id' => 'TXN-' . strtoupper(\Illuminate\Support\Str::random(10)),
        ], $user);

        return back()->with('success', "Payment of {$order->currency} {$request->amount} recorded! Receipt {$result['receipt']->receipt_number} issued.");
    }

    public function showReceipt($orderId, $receiptId)
    {
        $user = auth()->user();
        $order = ServiceOrder::findOrFail($orderId);
        abort_unless($order->customer_id === $user->id, 403);

        $receipt = Receipt::with(['customer', 'invoice', 'payment', 'order.service'])
            ->where('service_order_id', $order->id)
            ->findOrFail($receiptId);

        return view('admin.receipts.show', compact('receipt'));
    }
}
