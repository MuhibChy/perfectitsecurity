@extends('layouts.app')
@section('page-title', 'My Earnings')
@section('content')
<div class="space-y-6">
    <x-page-header title="My Earnings" subtitle="Salary, commission and project earnings from authoritative records. Model: {{ $summary['compensation'] }}." sys="STAFF://EARNINGS" />

    <div class="term-panel p-6">
        <dl class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="term-panel-2 p-4">
                <dt class="fin-tag fin-tag-profit">Total earned</dt>
                <dd class="fin-value mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($summary['total_earned'], 2) }}</dd>
            </div>
            <div class="term-panel-2 p-4">
                <dt class="fin-tag fin-tag-income">Total paid</dt>
                <dd class="fin-value mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($summary['total_paid'], 2) }}</dd>
            </div>
            <div class="term-panel-2 p-4">
                <dt class="fin-tag fin-tag-due">Outstanding</dt>
                <dd class="fin-value mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($summary['outstanding'], 2) }}</dd>
            </div>
            <div class="term-panel-2 p-4">
                <dt class="fin-tag fin-tag-expense">Commission outstanding</dt>
                <dd class="fin-value mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($summary['commission_outstanding'], 2) }}</dd>
            </div>
        </dl>
        <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div>Salary earned: <strong class="fin-value">{{ number_format($summary['salary_earned'], 2) }}</strong></div>
            <div>Salary paid: <strong class="fin-value">{{ number_format($summary['salary_paid'], 2) }}</strong></div>
            <div>Commission earned: <strong class="fin-value">{{ number_format($summary['commission_earned'], 2) }}</strong></div>
            <div>Commission paid: <strong class="fin-value">{{ number_format($summary['commission_paid'], 2) }}</strong></div>
            <div>Project agreed: <strong class="fin-value">{{ number_format($summary['project_agreed'], 2) }}</strong></div>
            <div>Project paid: <strong class="fin-value">{{ number_format($summary['project_paid'], 2) }}</strong></div>
            <div>Bonuses: <strong class="fin-value">{{ number_format($summary['bonuses'], 2) }}</strong></div>
            <div>Deductions: <strong class="fin-value">{{ number_format($summary['deductions'], 2) }}</strong></div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="term-panel p-6">
            <h2 class="font-mono text-[11px] tracking-[0.2em] uppercase text-slate-500 dark:text-term-700 mb-3">Salary records</h2>
            <div class="term-table-wrap"><table class="term-table">
                <thead><tr><th>Ref</th><th>Period</th><th>Net</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($salaries as $s)
                    <tr><td class="mono-id">{{ $s->salary_number }}</td><td>{{ $s->period }} · {{ optional($s->pay_date)->format('Y-m-d') }}</td><td class="fin-value">{{ number_format($s->net_salary, 2) }}</td><td><x-status-badge :status="$s->status" /></td></tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-slate-500">No salary records.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
            <div class="mt-3">{{ $salaries->links() }}</div>
        </div>

        <div class="term-panel p-6">
            <h2 class="font-mono text-[11px] tracking-[0.2em] uppercase text-slate-500 dark:text-term-700 mb-3">Commissions</h2>
            <div class="term-table-wrap"><table class="term-table">
                <thead><tr><th>Ref</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($commissions as $c)
                    <tr><td class="mono-id">{{ $c->commission_number }}</td><td class="fin-value">{{ number_format($c->commission_amount, 2) }}</td><td><x-status-badge :status="$c->status" /></td></tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-slate-500">No commissions.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
            <div class="mt-3">{{ $commissions->links() }}</div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="term-panel p-6">
            <h2 class="font-mono text-[11px] tracking-[0.2em] uppercase text-slate-500 dark:text-term-700 mb-3">Payouts</h2>
            <ul class="space-y-2 text-sm">
                @forelse($payouts as $p)
                <li class="flex items-center justify-between gap-2 border-b border-slate-100 dark:border-white/5 pb-2">
                    <span class="font-mono text-slate-600 dark:text-term-800">{{ $p->payout_number }}</span>
                    <span class="fin-value">{{ number_format($p->amount, 2) }}</span>
                    <x-status-badge :status="$p->status" />
                </li>
                @empty
                <li class="text-slate-500">No payouts.</li>
                @endforelse
            </ul>
        </div>
        <div class="term-panel p-6">
            <h2 class="font-mono text-[11px] tracking-[0.2em] uppercase text-slate-500 dark:text-term-700 mb-3">Project involvement</h2>
            <ul class="space-y-2 text-sm">
                @forelse($projects as $p)
                <li class="border-b border-slate-100 dark:border-white/5 pb-2">
                    <span class="font-semibold text-slate-900 dark:text-white">{{ $p->project_number }} · {{ $p->name }}</span>
                    <span class="text-slate-500 dark:text-term-700">· {{ $p->status }}</span>
                </li>
                @empty
                <li class="text-slate-500">No projects.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
