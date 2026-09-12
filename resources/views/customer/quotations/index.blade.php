@extends('layouts.app')

@section('title', 'My Quotations — Customer Portal')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Service Quotations"
        subtitle="Review, approve, or decline commercial estimates and formal service proposals from our engineering staff."
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Quotations' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('portal.service-request.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Request New Quote
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Quotations"
            :value="$quotations->total()"
            color="blue"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
        />
        <x-stat-card
            title="Pending Review"
            :value="$quotations->where('status', 'sent')->count()"
            color="amber"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-stat-card
            title="Accepted Proposals"
            :value="$quotations->where('status', 'accepted')->count()"
            color="emerald"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-stat-card
            title="Total Proposed"
            :value="'$' . number_format($quotations->sum('total'), 2)"
            color="purple"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>'
        />
    </div>

    {{-- Quotations Table Card --}}
    <div class="glass-card overflow-hidden">
        @if($quotations->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Quote #</th>
                        <th>Status</th>
                        <th>Subtotal</th>
                        <th>Discount / Tax</th>
                        <th>Final Total</th>
                        <th>Valid Until</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotations as $quote)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                        <td>
                            <a href="{{ route('portal.quotations.show', $quote->id) }}" class="font-mono font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                                {{ $quote->quotation_number ?? 'QUO-' . str_pad($quote->id, 5, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td>
                            <x-status-badge :status="$quote->status" />
                        </td>
                        <td class="font-mono text-gray-600 dark:text-gray-300">
                            ${{ number_format($quote->subtotal ?? 0, 2) }}
                        </td>
                        <td class="text-xs text-gray-500">
                            @if($quote->discount_amount > 0)
                                <span class="text-emerald-600">-${{ number_format($quote->discount_amount, 2) }}</span> /
                            @endif
                            Tax {{ $quote->tax_rate ?? 0 }}%
                        </td>
                        <td class="font-mono font-bold text-gray-900 dark:text-white">
                            ${{ number_format($quote->total ?? 0, 2) }}
                        </td>
                        <td class="text-xs {{ $quote->valid_until && $quote->valid_until->isPast() ? 'text-rose-500 font-semibold' : 'text-gray-500' }}">
                            {{ $quote->valid_until ? $quote->valid_until->format('M d, Y') : 'Indefinite' }}
                        </td>
                        <td class="text-right">
                            <a href="{{ route('portal.quotations.show', $quote->id) }}" class="btn-secondary btn-sm">
                                View Details &rarr;
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5">
            {{ $quotations->links() }}
        </div>
        @else
        <x-empty-state
            title="No Quotations on Record"
            message="When our engineering team drafts a formal commercial estimate or service proposal, it will appear here for your review."
            actionText="Request a Consultation"
            :actionUrl="route('portal.service-request.create')"
        />
        @endif
    </div>
</div>
@endsection
