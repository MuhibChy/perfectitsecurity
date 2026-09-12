@extends('layouts.app')

@section('title', 'Quotation ' . ($quotation->quotation_number ?? $quotation->id) . ' — Customer Portal')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <x-page-header
        :title="'Quotation ' . ($quotation->quotation_number ?? 'QUO-' . str_pad($quotation->id, 5, '0', STR_PAD_LEFT))"
        subtitle="Formal estimate for specialized IT infrastructure and engineering services."
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Quotations' => route('portal.quotations.index'), 'View' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('portal.quotations.pdf', $quotation->id) }}" class="btn-secondary btn-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download PDF
            </a>
            <a href="{{ route('portal.quotations.index') }}" class="btn-ghost btn-sm">
                &larr; Back to List
            </a>
            @if($quotation->status === 'sent' && (!$quotation->valid_until || $quotation->valid_until->isFuture()))
            <form action="{{ route('portal.quotations.reject', $quotation->id) }}" method="POST" class="inline" onsubmit="return confirm('Decline this quotation proposal?')">
                @csrf
                <button type="submit" class="btn-secondary text-rose-600 border-rose-200 dark:border-rose-900/40 hover:bg-rose-50 btn-sm">
                    Decline Proposal
                </button>
            </form>
            <form action="{{ route('portal.quotations.accept', $quotation->id) }}" method="POST" class="inline" onsubmit="return confirm('Accept this quotation proposal? This confirms commercial approval.')">
                @csrf
                <button type="submit" class="btn-primary btn-sm bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Accept & Approve Quote
                </button>
            </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-sm flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Main Quotation Sheet --}}
    <div class="glass-card p-8 lg:p-10 rounded-2xl shadow-xl border border-white/10 space-y-8">
        {{-- Header Info --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 pb-8 border-b border-gray-100 dark:border-white/5">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Proposal Reference</span>
                <h2 class="text-2xl font-mono font-bold text-gray-900 dark:text-white mt-1">
                    {{ $quotation->quotation_number ?? 'QUO-' . str_pad($quotation->id, 5, '0', STR_PAD_LEFT) }}
                </h2>
                <div class="mt-2">
                    <x-status-badge :status="$quotation->status" />
                </div>
            </div>

            <div class="text-left sm:text-right space-y-1 text-xs text-gray-500 dark:text-gray-400">
                <p>Date Created: <strong class="text-gray-800 dark:text-gray-200">{{ $quotation->created_at->format('M d, Y') }}</strong></p>
                <p>Valid Until: <strong class="{{ $quotation->valid_until && $quotation->valid_until->isPast() ? 'text-rose-500' : 'text-gray-800 dark:text-gray-200' }}">{{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : 'Open validity' }}</strong></p>
            </div>
        </div>

        {{-- Line Items Table --}}
        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white mb-4">Specified Scope & Deliverables</h3>
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-white/5">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Discount</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotation->items as $item)
                        <tr>
                            <td class="font-medium text-gray-900 dark:text-white">
                                {{ $item->description }}
                            </td>
                            <td class="text-center font-mono">
                                {{ $item->quantity }}
                            </td>
                            <td class="text-right font-mono text-gray-600 dark:text-gray-300">
                                ${{ number_format($item->unit_price, 2) }}
                            </td>
                            <td class="text-right font-mono text-xs text-gray-500">
                                {{ $item->discount > 0 ? '-$' . number_format($item->discount, 2) : '—' }}
                            </td>
                            <td class="text-right font-mono font-bold text-gray-900 dark:text-white">
                                ${{ number_format(($item->quantity * $item->unit_price) - ($item->discount ?? 0), 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 py-6">
                                Scope of work detailed under agreement summary.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Totals Summary Grid --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-8 pt-6 border-t border-gray-100 dark:border-white/5">
            <div class="max-w-md text-xs text-gray-500 space-y-3">
                @if($quotation->notes)
                <div>
                    <h4 class="font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Engineering Notes</h4>
                    <p class="leading-relaxed">{{ $quotation->notes }}</p>
                </div>
                @endif
                @if($quotation->terms)
                <div>
                    <h4 class="font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Commercial Terms</h4>
                    <p class="leading-relaxed">{{ $quotation->terms }}</p>
                </div>
                @endif
            </div>

            <div class="w-full sm:w-72 p-5 rounded-xl bg-surface-50 dark:bg-navy-800/60 border border-surface-200 dark:border-white/10 space-y-2.5 text-sm">
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Subtotal:</span>
                    <span class="font-mono font-medium">${{ number_format($quotation->subtotal ?? 0, 2) }}</span>
                </div>
                @if($quotation->discount_amount > 0)
                <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                    <span>Discount:</span>
                    <span class="font-mono font-medium">-${{ number_format($quotation->discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Tax ({{ $quotation->tax_rate ?? 0 }}%):</span>
                    <span class="font-mono font-medium">${{ number_format($quotation->tax ?? 0, 2) }}</span>
                </div>
                <div class="pt-3 border-t border-gray-200 dark:border-white/10 flex justify-between text-base font-bold text-gray-900 dark:text-white">
                    <span>Total Estimated:</span>
                    <span class="font-mono text-primary-600 dark:text-primary-400">${{ number_format($quotation->total ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
