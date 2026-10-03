@extends('layouts.app')
@section('title', 'My Service History')
@section('page-title', 'Service History')

@section('content')
<x-page-header title="My Service History" subtitle="Your services, orders, projects, tickets, invoices and payments in one chronological place." sys="HISTORY://TIMELINE" num="05">
        <x-slot:actions>
            <a href="{{ route('portal.reports.mine', ['type' => 'customer-full', 'format' => 'pdf']) }}" class="term-btn term-btn-ghost term-btn-sm">Generate Report</a>
        </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([['Services',$overview['services']],['Orders',$overview['orders']],['Projects',$overview['projects']],['Open tickets',$overview['tickets_open']],['Invoices',$overview['invoices']],['Paid (£)',$overview['paid_total']],['Outstanding (£)',$overview['outstanding']],['Documents',$overview['documents']]] as [$label,$val])
    <div class="term-panel-2 p-5"><div class="text-2xl font-bold text-slate-900 dark:text-white tabular-nums">{{ $val }}</div><div class="font-mono text-[11px] uppercase tracking-[0.18em] text-slate-500 dark:text-term-700 mt-1">{{ $label }}</div></div>
    @endforeach
</div>

<h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">My Timeline</h2>
<p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Times shown in {{ \App\Support\UserTime::for(auth()->user()) }} (stored in UTC).</p>
<x-timeline :timeline="$timeline" />
@endsection
