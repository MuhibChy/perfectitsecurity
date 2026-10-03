@extends('layouts.app')
@section('page-title', 'My Commissions')
@section('content')
<div class="space-y-6">
<x-page-header title="My Commissions" :subtitle="'Paid ' . number_format($earnings['paid'] ?? 0, 2)" sys="WORKSPACE://PAY">
    <x-slot:actions>
        <a href="{{ route('workspace.commissions.report', ['format' => 'pdf']) }}" class="term-btn term-btn-sm">Commission Report</a>
        <a href="{{ route('workspace.commissions.report', ['format' => 'csv']) }}" class="term-btn term-btn-ghost term-btn-sm">CSV</a>
    </x-slot:actions>
</x-page-header>
<div class="term-panel p-6 mb-4">
    <p class="text-sm text-slate-600 dark:text-term-800">Paid: <strong class="tabular-nums"><span class="fin-tag fin-tag-income">{{ number_format($earnings['paid'] ?? 0, 2) }}</span></strong> · Pending states: <strong class="tabular-nums"><span class="fin-tag fin-tag-expense">{{ number_format(($earnings['pending'] ?? 0) + ($earnings['submitted'] ?? 0) + ($earnings['under_review'] ?? 0) + ($earnings['approved'] ?? 0) + ($earnings['payable'] ?? 0), 2) }}</span></strong></p>
</div>
<div class="term-panel p-6 mb-4">
    <h3 class="font-bold mb-2 text-slate-900 dark:text-white">Commission records</h3>
    @forelse($commissions as $c)
        <div class="text-sm border-t border-white/5 py-2 text-slate-600 dark:text-term-800 font-mono">{{ $c->commission_number }} · {{ $c->commission_amount }} · {{ $c->status }}/{{ $c->payment_status }}</div>
    @empty<p class="text-sm text-slate-600 dark:text-term-800">None.</p>@endforelse
    <div class="mt-3">{{ $commissions->links() }}</div>
</div>
<div class="term-panel p-6">
    <h3 class="font-bold mb-2 text-slate-900 dark:text-white">Payouts</h3>
    @forelse($payouts as $p)
        <div class="text-sm border-t border-white/5 py-1 text-slate-600 dark:text-term-800 font-mono">{{ $p->payout_number ?? ('Payout #' . $p->id) }} · {{ $p->amount }} · {{ $p->status }}</div>
    @empty<p class="text-sm text-slate-600 dark:text-term-800">None.</p>@endforelse
</div>
</div>
@endsection
