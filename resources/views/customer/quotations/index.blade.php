@extends('layouts.app')

@section('title', 'My Quotations — Customer Portal')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Service Quotations"
        subtitle="Review, approve, or decline commercial estimates and formal service proposals from our engineering staff."
        sys="ORDER://QUOTES"
        num="11"
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Quotations' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('portal.service-request.create') }}" class="term-btn term-btn-sm">
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
    <div class="term-panel overflow-hidden">
        @if($quotations->count() > 0)
        <div class="term-table-wrap !border-0">
            <table class="data-table term-table term-table-cards">
                <thead>
                    <tr>
                        <th>Quote</th>
                        <th>Status</th>
                        <th>Subtotal</th>
                        <th>Tax</th>
                        <th>Total</th>
                        <th>Valid</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotations as $quote)
                    <tr>
                        <td data-label="Quote">
                            <a href="{{ route('portal.quotations.show', $quote->id) }}" class="font-mono font-semibold text-accent-soft hover:underline">
                                {{ $quote->quotation_number ?? 'QUO-' . str_pad($quote->id, 5, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td data-label="Status">
                            <x-status-badge :status="$quote->status" />
                        </td>
                        <td data-label="Subtotal" class="font-mono tabular-nums">
                            ${{ number_format($quote->subtotal ?? 0, 2) }}
                        </td>
                        <td data-label="Tax" class="font-mono text-[11px] text-slate-600 dark:text-term-800">
                            @if($quote->discount_amount > 0)
                                <span class="fin-tag fin-tag-income">-${{ number_format($quote->discount_amount, 2) }}</span> /
                            @endif
                            Tax {{ $quote->tax_rate ?? 0 }}%
                        </td>
                        <td data-label="Total" class="font-mono font-bold tabular-nums">
                            ${{ number_format($quote->total ?? 0, 2) }}
                        </td>
                        <td data-label="Valid" class="font-mono text-[11px] {{ $quote->valid_until && $quote->valid_until->isPast() ? 'text-red-400 font-semibold' : 'text-slate-600 dark:text-term-800' }}">
                            {{ $quote->valid_until ? $quote->valid_until->format('M d, Y') : 'Indefinite' }}
                        </td>
                        <td data-label="Action" class="text-right">
                            <a href="{{ route('portal.quotations.show', $quote->id) }}" class="term-btn term-btn-ghost term-btn-sm">
                                View Details &rarr;
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-white/10">
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
