<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::where('customer_id', auth()->id())->latest()->paginate(15);
        return view('customer.invoices.index', compact('invoices'));
    }

    public function show($id)
    {
        $invoice = Invoice::where('customer_id', auth()->id())->with('items', 'payments')->findOrFail($id);
        return view('customer.invoices.show', compact('invoice'));
    }

    public function pdf($id)
    {
        $invoice = Invoice::where('customer_id', auth()->id())->with('items', 'payments')->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facades\Pdf::loadView('customer.invoices.pdf', compact('invoice'))
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }
}
