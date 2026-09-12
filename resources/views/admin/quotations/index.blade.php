@extends('layouts.app')

@section('title', 'Quotations & Commercial Proposals — Admin Portal')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Quotations Management"
        subtitle="Author, send, and convert formal commercial estimates and engineering proposals."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Quotations' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.quotations.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Draft New Quotation
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Proposals"
            :value="$quotations->total()"
            color="blue"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
        />
        <x-stat-card
            title="Sent / Awaiting Approval"
            :value="$quotations->where('status', 'sent')->count()"
            color="amber"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-stat-card
            title="Accepted by Client"
            :value="$quotations->where('status', 'accepted')->count()"
            color="emerald"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-stat-card
            title="Proposal Pipeline"
            :value="'$' . number_format($quotations->sum('total'), 2)"
            color="purple"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>'
        />
    </div>

    {{-- Filter Card --}}
    <div class="glass-card p-4 rounded-2xl flex items-center gap-2 overflow-x-auto scrollbar-none">
        @php $currStatus = request('status'); @endphp
        <a href="{{ route('admin.quotations.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ !$currStatus ? 'bg-primary-600 text-white' : 'bg-surface-100 dark:bg-navy-800 text-gray-600 dark:text-gray-300' }}">
            All Quotations
        </a>
        @foreach(['draft' => 'Drafts', 'sent' => 'Sent', 'accepted' => 'Accepted', 'converted' => 'Converted', 'rejected' => 'Rejected'] as $stKey => $stLabel)
        <a href="{{ route('admin.quotations.index', ['status' => $stKey]) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ $currStatus === $stKey ? 'bg-primary-600 text-white' : 'bg-surface-100 dark:bg-navy-800 text-gray-600 dark:text-gray-300 hover:bg-surface-200' }}">
            {{ $stLabel }}
        </a>
        @endforeach
    </div>

    {{-- Quotations Table --}}
    <div class="glass-card overflow-hidden">
        @if($quotations->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Quote Ref</th>
                        <th>Client / Organization</th>
                        <th>Status</th>
                        <th>Subtotal</th>
                        <th>Total Value</th>
                        <th>Valid Until</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotations as $quote)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                        <td>
                            <a href="{{ route('admin.quotations.show', $quote) }}" class="font-mono font-bold text-primary-600 dark:text-primary-400 hover:underline">
                                {{ $quote->quotation_number ?? 'QUO-' . str_pad($quote->id, 5, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $quote->customer->name ?? 'Direct Client' }}</div>
                            <div class="text-xs text-gray-500">{{ $quote->customer->email ?? '' }}</div>
                        </td>
                        <td>
                            <x-status-badge :status="$quote->status" />
                        </td>
                        <td class="font-mono text-gray-600 dark:text-gray-300">
                            ${{ number_format($quote->subtotal ?? 0, 2) }}
                        </td>
                        <td class="font-mono font-bold text-gray-900 dark:text-white">
                            ${{ number_format($quote->total ?? 0, 2) }}
                        </td>
                        <td class="text-xs {{ $quote->valid_until && $quote->valid_until->isPast() ? 'text-rose-500 font-semibold' : 'text-gray-500' }}">
                            {{ $quote->valid_until ? $quote->valid_until->format('M d, Y') : 'Open' }}
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.quotations.show', $quote) }}" class="btn-ghost btn-sm text-xs">
                                    View
                                </a>
                                @if(in_array($quote->status, ['draft', 'sent']))
                                <a href="{{ route('admin.quotations.edit', $quote) }}" class="btn-secondary btn-sm text-xs">
                                    Edit
                                </a>
                                @endif
                                @if($quote->status === 'accepted')
                                <form action="{{ route('admin.quotations.convert', $quote) }}" method="POST" class="inline" onsubmit="return confirm('Generate invoice from this accepted quotation?')">
                                    @csrf
                                    <button type="submit" class="btn-primary btn-sm text-xs bg-emerald-600 hover:bg-emerald-700">
                                        Convert to Invoice
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5">
            {{ $quotations->withQueryString()->links() }}
        </div>
        @else
        <x-empty-state
            title="No Quotations Found"
            message="No commercial estimates found for the selected status filter."
            actionText="Draft a Quotation"
            :actionUrl="route('admin.quotations.create')"
        />
        @endif
    </div>
</div>
@endsection
