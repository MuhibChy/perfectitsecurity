<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Quotation;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::where('customer_id', auth()->id())->latest()->paginate(15);

        return view('customer.quotations.index', compact('quotations'));
    }

    public function show($id)
    {
        $quotation = Quotation::where('customer_id', auth()->id())->with('items')->findOrFail($id);

        return view('customer.quotations.show', compact('quotation'));
    }

    public function accept($id, \App\Services\ServiceOrderWorkflowService $orders)
    {
        $quotation = Quotation::where('customer_id', auth()->id())->findOrFail($id);
        $order = $orders->createOrderFromQuotation($quotation, auth()->user());

        return redirect()->route('portal.orders.show', $order->id)
            ->with('success', "Quotation accepted! Order {$order->order_number} created.");
    }

    public function reject($id)
    {
        $quotation = Quotation::where('customer_id', auth()->id())->findOrFail($id);
        abort_unless($quotation->status === 'sent', 422, 'This quotation is not available for rejection.');
        $quotation->update(['status' => 'rejected']);

        return redirect()->back()->with('info', 'Quotation rejected.');
    }

    public function pdf($id)
    {
        $quotation = Quotation::where('customer_id', auth()->id())
            ->with(['items', 'customer', 'company'])
            ->findOrFail($id);

        // Local-only PDF: no remote assets — remote disabled to prevent SSRF.
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('customer.quotations.pdf', compact('quotation'))
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', false);

        return $pdf->download('quotation-'.$quotation->quotation_number.'.pdf');
    }
}
