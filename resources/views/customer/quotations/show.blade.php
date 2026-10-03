@extends('layouts.app')

@section('title', 'Quotation ' . ($quotation->quotation_number ?? $quotation->id) . ' — Customer Portal')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <x-page-header
        :title="'Quotation ' . ($quotation->quotation_number ?? 'QUO-' . str_pad($quotation->id, 5, '0', STR_PAD_LEFT))"
        subtitle="Formal estimate for specialized IT infrastructure and engineering services."
        sys="ORDER://QUOTES"
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Quotations' => route('portal.quotations.index'), 'View' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('portal.quotations.pdf', $quotation->id) }}" class="term-btn term-btn-ghost term-btn-sm">
                Download PDF
            </a>
            <a href="{{ route('portal.quotations.index') }}" class="term-btn term-btn-ghost term-btn-sm">
                &larr; Back to List
            </a>
            @if($quotation->status === 'sent' && (!$quotation->valid_until || $quotation->valid_until->isFuture()))
            <form action="{{ route('portal.quotations.reject', $quotation->id) }}" method="POST" class="inline" onsubmit="return confirm('Decline this quotation proposal?')">
                @csrf
                <button type="submit" class="btn btn-destructive btn-sm">
                    Decline Proposal
                </button>
            </form>
            <form action="{{ route('portal.quotations.accept', $quotation->id) }}" method="POST" class="inline" onsubmit="return confirm('Accept this quotation proposal? This confirms commercial approval.')">
                @csrf
                <button type="submit" class="term-btn term-btn-sm">
                    Accept &amp; Approve Quote
                </button>
            </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
    <div class="term-alert term-alert-ok">
        <span class="term-alert-tag">OK</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Main Quotation Sheet --}}
    <div class="term-panel p-8 lg:p-10 space-y-8">
        {{-- Header Info --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 pb-8 border-b border-white/10">
            <div>
                <span class="term-field-label">Proposal Reference</span>
                <h2 class="text-2xl font-mono font-bold text-slate-900 dark:text-white mt-1">
                    {{ $quotation->quotation_number ?? 'QUO-' . str_pad($quotation->id, 5, '0', STR_PAD_LEFT) }}
                </h2>
                <div class="mt-2">
                    <x-status-badge :status="$quotation->status" />
                </div>
            </div>

            <div class="text-left sm:text-right space-y-1 font-mono text-[11px] uppercase tracking-[0.12em] text-slate-600 dark:text-term-800">
                <p>Created: <strong class="text-slate-900 dark:text-white">{{ $quotation->created_at->format('M d, Y') }}</strong></p>
                <p>Valid: <strong class="{{ $quotation->valid_until && $quotation->valid_until->isPast() ? 'text-red-400' : 'text-slate-900 dark:text-white' }}">{{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : 'Open validity' }}</strong></p>
            </div>
        </div>

        {{-- Line Items Table --}}
        <div>
            <h3 class="term-field-label mb-4">Specified Scope &amp; Deliverables</h3>
            <div class="term-table-wrap">
                <table class="data-table term-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Unit</th>
                            <th class="text-right">Disc</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotation->items as $item)
                        <tr>
                            <td data-label="Item" class="font-medium text-slate-900 dark:text-white">
                                {{ $item->description }}
                            </td>
                            <td data-label="Qty" class="text-center font-mono">
                                {{ $item->quantity }}
                            </td>
                            <td data-label="Unit" class="text-right font-mono tabular-nums">
                                ${{ number_format($item->unit_price, 2) }}
                            </td>
                            <td data-label="Disc" class="text-right font-mono text-xs">
                                {{ $item->discount > 0 ? '-$' . number_format($item->discount, 2) : '—' }}
                            </td>
                            <td data-label="Amount" class="text-right font-mono font-bold tabular-nums">
                                ${{ number_format(($item->quantity * $item->unit_price) - ($item->discount ?? 0), 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-600 dark:text-term-800 py-6">
                                Scope of work detailed under agreement summary.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Totals Summary Grid --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-8 pt-6 border-t border-white/10">
            <div class="max-w-md text-xs text-slate-600 dark:text-term-800 space-y-3">
                @if($quotation->notes)
                <div>
                    <h4 class="term-field-label mb-1">Engineering Notes</h4>
                    <p class="leading-relaxed">{{ $quotation->notes }}</p>
                </div>
                @endif
                @if($quotation->terms)
                <div>
                    <h4 class="term-field-label mb-1">Commercial Terms</h4>
                    <p class="leading-relaxed">{{ $quotation->terms }}</p>
                </div>
                @endif
            </div>

            <div class="w-full sm:w-72 term-panel-2 p-5 space-y-2.5 text-sm">
                <div class="flex justify-between text-slate-600 dark:text-term-800">
                    <span>Subtotal:</span>
                    <span class="font-mono font-medium tabular-nums">${{ number_format($quotation->subtotal ?? 0, 2) }}</span>
                </div>
                @if($quotation->discount_amount > 0)
                <div class="flex justify-between">
                    <span>Discount:</span>
                    <span class="font-mono font-medium tabular-nums"><span class="fin-tag fin-tag-income">-${{ number_format($quotation->discount_amount, 2) }}</span></span>
                </div>
                @endif
                <div class="flex justify-between text-slate-600 dark:text-term-800">
                    <span>Tax ({{ $quotation->tax_rate ?? 0 }}%):</span>
                    <span class="font-mono font-medium tabular-nums">${{ number_format($quotation->tax ?? 0, 2) }}</span>
                </div>
                <div class="pt-3 border-t border-white/10 flex justify-between text-base font-bold text-slate-900 dark:text-white">
                    <span>Total Estimated:</span>
                    <span class="font-mono tabular-nums"><span class="fin-tag fin-tag-profit">${{ number_format($quotation->total ?? 0, 2) }}</span></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
