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

    public function accept($id)
    {
        $quotation = Quotation::where('customer_id', auth()->id())->findOrFail($id);
        abort_unless($quotation->status === 'sent' && (!$quotation->valid_until || $quotation->valid_until->isFuture()), 422, 'This quotation is not available for acceptance.');
        $quotation->update(['status' => 'accepted', 'accepted_at' => now()]);
        return redirect()->back()->with('success', 'Quotation accepted!');
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('customer.quotations.pdf', compact('quotation'))
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        return $pdf->download('quotation-' . $quotation->quotation_number . '.pdf');
    }
}

