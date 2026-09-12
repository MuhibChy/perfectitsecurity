<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::with('customer');
        if ($request->status) $query->where('status', $request->status);
        $quotations = $query->latest()->paginate(20);
        return view('admin.quotations.index', compact('quotations'));
    }

    public function create()
    {
        $customers = User::customers()->get();
        $services = Service::where('is_active', true)->get();
        return view('admin.quotations.create', compact('customers', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'valid_until' => 'required|date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        $quotation = Quotation::create([
            'customer_id' => $validated['customer_id'],
            'notes' => $validated['notes'] ?? null,
            'terms' => $validated['terms'] ?? null,
            'valid_until' => $validated['valid_until'],
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'status' => 'draft',
        ]);

        foreach ($validated['items'] as $item) {
            $itemTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
            $quotation->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'] ?? 0,
                'total' => $itemTotal,
            ]);
        }

        $quotation->subtotal = $quotation->items->sum('total');
        $quotation->tax_amount = $quotation->subtotal * ($quotation->tax_rate / 100);
        $quotation->total = $quotation->subtotal + $quotation->tax_amount;
        $quotation->save();

        return redirect()->route('admin.quotations.index')->with('success', 'Quotation created!');
    }

    public function show($id)
    {
        $quotation = Quotation::with('customer', 'items')->findOrFail($id);
        return view('admin.quotations.show', compact('quotation'));
    }

    public function edit($id)
    {
        $quotation = Quotation::with('items')->findOrFail($id);
        $customers = User::customers()->get();
        $services = Service::where('is_active', true)->get();
        return view('admin.quotations.edit', compact('quotation', 'customers', 'services'));
    }

    public function update(Request $request, $id)
    {
        $quotation = Quotation::findOrFail($id);
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'valid_until' => 'required|date',
        ]);

        $quotation->update($validated);
        return redirect()->route('admin.quotations.index')->with('success', 'Quotation updated!');
    }

    public function destroy($id)
    {
        Quotation::findOrFail($id)->delete();
        return redirect()->route('admin.quotations.index')->with('success', 'Quotation deleted.');
    }

    public function send(Request $request, $id)
    {
        $quotation = Quotation::findOrFail($id);
        $quotation->update(['status' => 'sent', 'sent_at' => now()]);
        return redirect()->back()->with('success', 'Quotation sent!');
    }

    public function convertToInvoice(Request $request, $id)
    {
        return DB::transaction(function () use ($id) {
        $quotation = Quotation::with('items')->lockForUpdate()->findOrFail($id);
        abort_unless($quotation->status === 'accepted', 422, 'Only accepted quotations can be converted to an invoice.');

        $invoice = Invoice::create([
            'customer_id' => $quotation->customer_id,
            'company_id' => $quotation->company_id,
            'quotation_id' => $quotation->id,
            'notes' => $quotation->notes,
            'terms' => $quotation->terms,
            'subtotal' => $quotation->subtotal,
            'discount_amount' => $quotation->discount_amount,
            'tax_rate' => $quotation->tax_rate,
            'tax_amount' => $quotation->tax_amount,
            'total' => $quotation->total,
            'amount_due' => $quotation->total,
            'status' => 'draft',
            'due_date' => now()->addDays(30),
        ]);

        foreach ($quotation->items as $item) {
            $invoice->items()->create([
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount,
                'total' => $item->total,
            ]);
        }

        $quotation->update(['status' => 'converted']);

        return redirect()->route('admin.invoices.index')->with('success', 'Quotation converted to invoice!');
        });
    }

    public function pdf($id)
    {
        $quotation = Quotation::with(['items', 'customer', 'company'])->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('customer.quotations.pdf', compact('quotation'))
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        return $pdf->download('quotation-' . $quotation->quotation_number . '.pdf');
    }
}
