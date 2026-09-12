<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\Service;
use App\Services\FinancialService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('customer', 'project');
        if ($request->status) $query->where('status', $request->status);
        if ($request->search) {
            $search = addcslashes($request->search, '%_\\');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }
        $invoices = $query->latest()->paginate(20);
        return view('admin.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = User::customers()->get();
        $services = Service::where('is_active', true)->get();
        return view('admin.invoices.create', compact('customers', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'due_date' => 'required|date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'in:fixed,percentage',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        $invoice = \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
            $invoice = Invoice::create([
                'customer_id' => $validated['customer_id'],
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'due_date' => $validated['due_date'],
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'discount_type' => $validated['discount_type'] ?? 'fixed',
                'status' => 'draft',
            ]);

            foreach ($validated['items'] as $item) {
                $itemTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'total' => $itemTotal,
                ]);
            }

            $invoice->recalculate();
            return $invoice;
        });
        AuditService::log('create', 'invoices', $invoice, 'Invoice created');

        return redirect()->route('admin.invoices.index')->with('success', 'Invoice created!');
    }

    public function edit($id)
    {
        $invoice = Invoice::with('items', 'customer')->findOrFail($id);
        $customers = User::customers()->get();
        $services = Service::where('is_active', true)->get();
        return view('admin.invoices.edit', compact('invoice', 'customers', 'services'));
    }

    public function show($id)
    {
        $invoice = Invoice::with('items', 'customer', 'payments')->findOrFail($id);
        return view('admin.invoices.show', compact('invoice'));
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::lockForUpdate()->findOrFail($id);
        abort_if(in_array($invoice->status, ['paid', 'cancelled'], true), 422, 'Paid or cancelled invoices cannot be edited.');
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'due_date' => 'required|date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'in:fixed,percentage',
            'items' => 'required|array|min:1',
        ]);

        $oldData = $invoice->toArray();

        \Illuminate\Support\Facades\DB::transaction(function () use ($invoice, $validated) {
            $invoice->update([
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'due_date' => $validated['due_date'],
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'discount_type' => $validated['discount_type'] ?? 'fixed',
            ]);

            $invoice->items()->delete();
            foreach ($validated['items'] as $item) {
                $itemTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'total' => $itemTotal,
                ]);
            }

            $invoice->recalculate();
        });
        AuditService::log('update', 'invoices', $invoice->fresh(), 'Invoice updated', $oldData, $invoice->fresh()->toArray());

        return redirect()->route('admin.invoices.index')->with('success', 'Invoice updated!');
    }

    public function send(Request $request, $id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);
        abort_if(in_array($invoice->status, ['sent', 'paid', 'cancelled'], true), 422, 'This invoice has already been sent or closed.');
        $invoice->update(['status' => 'sent', 'sent_at' => now(), 'issued_date' => now()]);

        // Send email notification to customer
        if ($invoice->customer) {
            $invoice->customer->notify(
                new \App\Notifications\InvoiceCreatedNotification($invoice, 'sent')
            );
        }

        AuditService::log('send', 'invoices', $invoice, 'Invoice sent to customer');

        return redirect()->back()->with('success', 'Invoice sent!');
    }

    public function pdf($id)
    {
        $invoice = Invoice::with('items', 'customer')->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facades\Pdf::loadView('admin.invoices.pdf', compact('invoice'))
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }
}
