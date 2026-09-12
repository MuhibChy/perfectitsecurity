@extends('layouts.app')

@section('title', 'Quotation ' . ($quotation->quotation_number ?? $quotation->id) . ' — Admin Portal')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <x-page-header
        :title="'Quotation ' . ($quotation->quotation_number ?? 'QUO-' . str_pad($quotation->id, 5, '0', STR_PAD_LEFT))"
        subtitle="Commercial proposal review, client dispatch, and conversion management."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Quotations' => route('admin.quotations.index'), 'View' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.quotations.pdf', $quotation) }}" class="btn-secondary btn-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download PDF
            </a>
            <a href="{{ route('admin.quotations.index') }}" class="btn-ghost btn-sm">
                &larr; Back
            </a>
            @if($quotation->status === 'draft')
            <a href="{{ route('admin.quotations.edit', $quotation) }}" class="btn-secondary btn-sm">
                Edit
            </a>
            <form action="{{ route('admin.quotations.send', $quotation) }}" method="POST" class="inline" onsubmit="return confirm('Send proposal to client email?')">
                @csrf
                <button type="submit" class="btn-primary btn-sm">
                    Send to Client
                </button>
            </form>
            @endif

            @if($quotation->status === 'accepted')
            <form action="{{ route('admin.quotations.convert', $quotation) }}" method="POST" class="inline" onsubmit="return confirm('Convert this accepted quotation into an active invoice?')">
                @csrf
                <button type="submit" class="btn-primary btn-sm bg-emerald-600 hover:bg-emerald-700">
                    Convert to Invoice &rarr;
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

    {{-- Main Quotation Document --}}
    <div class="glass-card p-8 lg:p-12 rounded-2xl shadow-xl border border-white/10 space-y-8">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 pb-8 border-b border-gray-100 dark:border-white/5">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Target Client</span>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $quotation->customer->name ?? 'Direct Client' }}</h3>
                <p class="text-xs text-gray-500 font-mono">{{ $quotation->customer->email ?? '' }}</p>
                @if($quotation->customer && $quotation->customer->company_name)
                <p class="text-xs text-gray-400 mt-0.5">{{ $quotation->customer->company_name }}</p>
                @endif
                <div class="mt-3">
                    <x-status-badge :status="$quotation->status" />
                </div>
            </div>

            <div class="text-left sm:text-right space-y-1 text-xs text-gray-500 dark:text-gray-400">
                <p>Quotation Ref: <strong class="font-mono text-gray-900 dark:text-white">{{ $quotation->quotation_number ?? 'QUO-' . $quotation->id }}</strong></p>
                <p>Created: <strong class="text-gray-800 dark:text-gray-200">{{ $quotation->created_at->format('M d, Y') }}</strong></p>
                <p>Valid Until: <strong class="{{ $quotation->valid_until && $quotation->valid_until->isPast() ? 'text-rose-500' : 'text-gray-800 dark:text-gray-200' }}">{{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : 'Open' }}</strong></p>
            </div>
        </div>

        {{-- Line Items --}}
        <div>
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Itemized Deliverables & Scope</h4>
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-white/5">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Deliverable Description</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-right">Unit Rate</th>
                            <th class="text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotation->items as $item)
                        <tr>
                            <td class="font-medium text-gray-900 dark:text-white">{{ $item->description }}</td>
                            <td class="text-center font-mono">{{ $item->quantity }}</td>
                            <td class="text-right font-mono text-gray-600 dark:text-gray-300">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right font-mono font-bold text-gray-900 dark:text-white">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-6 text-xs text-gray-500">No item lines defined.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Totals Grid --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-8 pt-6 border-t border-gray-100 dark:border-white/5">
            <div class="max-w-md text-xs text-gray-500 space-y-3">
                @if($quotation->notes)
                <div>
                    <h5 class="font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Notes</h5>
                    <p class="leading-relaxed">{{ $quotation->notes }}</p>
                </div>
                @endif
                @if($quotation->terms)
                <div>
                    <h5 class="font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Terms & Conditions</h5>
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
                <div class="flex justify-between text-emerald-600">
                    <span>Discount:</span>
                    <span class="font-mono font-medium">-${{ number_format($quotation->discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Tax ({{ $quotation->tax_rate ?? 0 }}%):</span>
                    <span class="font-mono font-medium">${{ number_format($quotation->tax ?? 0, 2) }}</span>
                </div>
                <div class="pt-3 border-t border-gray-200 dark:border-white/10 flex justify-between text-base font-bold text-gray-900 dark:text-white">
                    <span>Total Value:</span>
                    <span class="font-mono text-primary-600 dark:text-primary-400">${{ number_format($quotation->total ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
