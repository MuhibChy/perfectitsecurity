@extends('layouts.app')

@section('page-title', 'Sales Pipeline & Lead Management')

@section('content')
<div class="space-y-6">
    {{-- Header & Metrics --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </span>
                Visual Sales Pipeline
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Track incoming service requests, advance opportunities through commercial review stages, and graduate qualified leads to active quotations.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.service-requests.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Table View
            </a>
            <a href="{{ route('admin.quotations.create') }}" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Quotation
            </a>
        </div>
    </div>

    {{-- Metric Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="glass-card p-4 rounded-xl border border-gray-200 dark:border-white/10">
            <span class="text-xs font-medium uppercase tracking-wider text-gray-500">Total Leads</span>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalCount }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Across all pipeline stages</div>
        </div>

        <div class="glass-card p-4 rounded-xl border border-gray-200 dark:border-white/10">
            <span class="text-xs font-medium uppercase tracking-wider text-cyan-600 dark:text-cyan-400">Est. Pipeline Value</span>
            <div class="text-2xl font-bold text-cyan-600 dark:text-cyan-400 mt-1">${{ number_format($totalValue, 0) }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Combined budgets & quoted</div>
        </div>

        <div class="glass-card p-4 rounded-xl border border-gray-200 dark:border-white/10">
            <span class="text-xs font-medium uppercase tracking-wider text-amber-500">Active Review / Scoping</span>
            <div class="text-2xl font-bold text-amber-500 mt-1">
                {{ ($stages['inbound']['items']->count() ?? 0) + ($stages['scoping']['items']->count() ?? 0) + ($stages['pricing']['items']->count() ?? 0) }}
            </div>
            <div class="text-xs text-gray-400 mt-0.5">In negotiation & estimation</div>
        </div>

        <div class="glass-card p-4 rounded-xl border border-gray-200 dark:border-white/10">
            <span class="text-xs font-medium uppercase tracking-wider text-emerald-500">Won / Converted</span>
            <div class="text-2xl font-bold text-emerald-500 mt-1">
                {{ $stages['won']['items']->count() ?? 0 }}
            </div>
            <div class="text-xs text-gray-400 mt-0.5">Closed deals & accepted</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="glass-card p-4 rounded-xl border border-gray-200 dark:border-white/10">
        <form method="GET" action="{{ route('admin.service-requests.pipeline') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search Leads</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, company, email, or #..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
            </div>

            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Priority</label>
                <select name="priority" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                    <option value="">All Priorities</option>
                    @foreach(['urgent' => 'Urgent', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $pval => $plabel)
                        <option value="{{ $pval }}" {{ request('priority') === $pval ? 'selected' : '' }}>{{ $plabel }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[160px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Assigned Agent</label>
                <select name="assigned_to" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                    <option value="">All Staff</option>
                    @foreach($staffMembers as $staff)
                        <option value="{{ $staff->id }}" {{ request('assigned_to') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-medium rounded-lg text-sm transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.service-requests.pipeline') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Kanban Board Horizontal Scroll Container --}}
    <div class="overflow-x-auto pb-6">
        <div class="flex gap-4 min-w-[1400px]">
            @foreach($stages as $stageKey => $stage)
            <div class="flex-1 min-w-[280px] max-w-[340px] flex flex-col bg-gray-50/70 dark:bg-gray-900/50 rounded-2xl border border-gray-200/80 dark:border-white/10 p-3">
                {{-- Column Header --}}
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-200 dark:border-white/10">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-{{ $stage['color'] }}-500"></span>
                        <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $stage['title'] }}</h3>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                        {{ $stage['items']->count() }}
                    </span>
                </div>

                {{-- Column Cards --}}
                <div class="space-y-3 flex-1 overflow-y-auto max-h-[750px] pr-1">
                    @forelse($stage['items'] as $item)
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-white/5 hover:shadow-md transition-shadow group flex flex-col justify-between">
                        <div>
                            {{-- Top row: Ref & Priority --}}
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-mono text-xs font-bold text-gray-500 dark:text-gray-400">
                                    {{ $item->request_number }}
                                </span>
                                @php
                                    $pColors = [
                                        'urgent' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        'high' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                        'medium' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                        'low' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full {{ $pColors[$item->priority] ?? $pColors['low'] }}">
                                    {{ $item->priority ?? 'medium' }}
                                </span>
                            </div>

                            {{-- Prospect Info --}}
                            <h4 class="font-bold text-gray-900 dark:text-white text-sm leading-snug">
                                {{ $item->name }}
                            </h4>
                            @if($item->company)
                                <div class="text-xs text-cyan-600 dark:text-cyan-400 font-medium truncate mt-0.5">
                                    {{ $item->company }}
                                </div>
                            @endif
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                {{ $item->email }}
                            </div>

                            {{-- Service requested --}}
                            <div class="mt-3 pt-2.5 border-t border-gray-100 dark:border-white/5 flex items-center justify-between text-xs">
                                <span class="text-gray-500 dark:text-gray-400 truncate font-medium">
                                    {{ $item->service ? $item->service->name : 'General IT Consulting' }}
                                </span>
                            </div>

                            {{-- Budget / Value --}}
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-[11px] text-gray-400">Budget / Value:</span>
                                <span class="font-bold text-xs text-emerald-600 dark:text-emerald-400 font-mono">
                                    @if($item->quoted_price)
                                        ${{ number_format($item->quoted_price, 0) }} (Quoted)
                                    @elseif($item->budget)
                                        ${{ number_format($item->budget, 0) }}
                                    @else
                                        Custom Quote
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- Card Actions --}}
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-white/5 space-y-2">
                            {{-- Stage Mover --}}
                            <form action="{{ route('admin.service-requests.update-status', $item->id) }}" method="POST" class="w-full">
                                @csrf
                                <div class="flex items-center gap-1">
                                    <select name="review_status" onchange="this.form.submit()" class="w-full text-xs py-1 px-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                                        <option value="new" {{ ($item->review_status ?? 'new') === 'new' ? 'selected' : '' }}>Stage: Inbound / New</option>
                                        <option value="under_review" {{ $item->review_status === 'under_review' ? 'selected' : '' }}>Stage: Under Review</option>
                                        <option value="scope_clarification" {{ $item->review_status === 'scope_clarification' ? 'selected' : '' }}>Stage: Scoping Details</option>
                                        <option value="pricing_in_progress" {{ $item->review_status === 'pricing_in_progress' ? 'selected' : '' }}>Stage: Pricing Calculation</option>
                                        <option value="sent_to_customer" {{ $item->review_status === 'sent_to_customer' ? 'selected' : '' }}>Stage: Proposal Sent</option>
                                        <option value="accepted" {{ $item->review_status === 'accepted' ? 'selected' : '' }}>Stage: Accepted / Won</option>
                                        <option value="rejected" {{ $item->review_status === 'rejected' ? 'selected' : '' }}>Stage: Rejected / Lost</option>
                                    </select>
                                </div>
                            </form>

                            {{-- Graduation / View Buttons --}}
                            <div class="flex items-center gap-1.5">
                                @if($item->quotation_id)
                                    <a href="{{ route('admin.quotations.show', $item->quotation_id) }}" class="flex-1 py-1 px-2 text-center text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 hover:bg-indigo-100 transition-colors">
                                        View Quote &rarr;
                                    </a>
                                @else
                                    <form action="{{ route('admin.service-requests.graduate', $item->id) }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full py-1 px-2 text-center text-xs font-semibold rounded-lg bg-cyan-500 hover:bg-cyan-600 text-white transition-colors flex items-center justify-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Graduate to Quote
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('admin.service-requests.show', $item->id) }}" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Inspect Request">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-gray-400 rounded-xl border border-dashed border-gray-200 dark:border-gray-800">
                        No requests in this stage
                    </div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
