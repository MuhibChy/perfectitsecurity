@extends('layouts.app')
@section('page-title', ($kind === 'customer' ? 'Customer 360' : 'Employee 360') . ' — ' . $user->name)
@section('content')

    <x-page-header title="People" sys="SYSTEM://PEOPLE" />
{{-- Individual 360° profile. Every section renders from authoritative relations only.
     Financial blocks require $showFinance (finance role). Timeline uses at/label/detail/url. --}}
<div class="term-panel p-6 mb-4" x-data="{ tab: 'overview' }">
    <div class="flex flex-wrap items-center gap-4">
        <img src="{{ $user->avatar_url }}" alt="avatar" class="w-16 h-16 rounded-full ring-2 ring-primary-200" />
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white truncate">{{ $user->name }}</h2>
                <span class="term-tag">{{ $user->roleDisplayName() }}</span>
                <span class="term-tag {{ ($user->is_active ?? true) ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' }}">{{ ($user->is_active ?? true) ? 'Active' : 'Inactive' }}</span>
                <span class="term-tag">{{ $user->verification_status ?? 'pending' }}</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $user->email }} · {{ $user->country ?? '—' }} · TZ {{ $user->timezone ?? '—' }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                @if($kind === 'customer')
                    Customer #{{ $user->id }} · {{ $user->company_name ?? 'No company' }} · since {{ optional($user->created_at)->format('Y-m-d') }}
                @else
                    Employee #{{ $user->employee_number ?? $user->id }} · {{ $user->job_title ?? '—' }} · {{ $user->department ?? '—' }} · {{ $user->branch ?? '—' }}
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.search.index', ['q' => $user->email]) }}" class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700">Trace records</a>
            @if($kind === 'customer')
                <a href="{{ route('admin.history.customer', $user->id) }}" class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700">Full history</a>
                <a href="{{ route('admin.reports.export', ['type' => 'customer', 'format' => 'pdf', 'customer_id' => $user->id]) }}" class="term-btn term-btn-sm">Export PDF</a>
            @else
                <a href="{{ route('admin.reports.export', ['type' => 'employee-service', 'format' => 'pdf', 'employee_id' => $user->id]) }}" class="term-btn term-btn-sm">Service report</a>
            @endif
        </div>
    </div>

    {{-- Tab bar (role-gated sections only; customers never see internal/finance tabs here) --}}
    <div class="flex flex-wrap gap-1 mt-5 border-b border-gray-200 dark:border-gray-700 text-sm">
        @foreach(['overview' => 'Overview', 'services' => 'Services', 'activity' => 'Activity', 'communication' => 'Communication'] as $key => $label)
            <button type="button" @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400'" class="px-3 py-2 border-b-2 font-medium">{{ $label }}</button>
        @endforeach
        @if($showFinance)
            <button type="button" @click="tab = 'financial'" :class="tab === 'financial' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400'" class="px-3 py-2 border-b-2 font-medium">Financial</button>
        @endif
    </div>

    {{-- Overview: stat grid from the same authoritative aggregates --}}
    <div x-show="tab === 'overview'" class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
        @foreach($overview as $stat => $value)
            <div class="bg-gray-50 dark:bg-gray-800/60 p-3">
                <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', $stat) }}</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate" title="{{ is_scalar($value) ? $value : '' }}">{{ is_scalar($value) ? $value : (is_null($value) ? '—' : json_encode($value)) }}</p>
            </div>
        @endforeach
    </div>

    {{-- Services: multi-employee contributions share one authoritative task each --}}
    <div x-show="tab === 'services'" class="mt-4">
        <h3 class="font-bold mb-2 text-gray-900 dark:text-white">Service contributions</h3>
        @forelse($contributions as $task)
            <div class="text-sm border-t border-gray-200 dark:border-gray-700 py-2 flex flex-wrap gap-x-3">
                <a href="{{ route('admin.tasks.show', $task->id) }}" class="font-medium text-primary-600 dark:text-primary-400 hover:underline">{{ $task->title ?? ('Task #' . $task->id) }}</a>
                <span class="text-gray-500">Role: {{ $task->pivot->role ?? '—' }}</span>
                <span class="text-gray-500">Status: {{ $task->status }}</span>
                <span class="text-gray-500">Customer: {{ optional(optional($task->customer))->name ?? (optional(optional($task->project)->customer)->name ?? '—') }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">No direct task contributions recorded. History is generated from actual assignments only.</p>
        @endforelse
        @isset($links)
            @if($links->isNotEmpty())
                <h3 class="font-bold mt-4 mb-2 text-gray-900 dark:text-white">Employee ↔ customer links</h3>
                @foreach($links as $link)
                    <div class="text-sm border-t border-gray-200 dark:border-gray-700 py-1 text-gray-600 dark:text-gray-300">{{ $link['via'] }} → {{ $link['customer']->name ?? '—' }}</div>
                @endforeach
            @endif
        @endisset
    </div>

    {{-- Activity: unified timeline (user/system/employee/customer/financial/service/admin events) --}}
    <div x-show="tab === 'activity'" class="mt-4">
        <h3 class="font-bold mb-2 text-gray-900 dark:text-white">Timeline (latest 100)</h3>
        @forelse($timeline as $event)
            <div class="text-xs border-t border-gray-200 dark:border-gray-700 py-1.5 flex flex-wrap gap-x-2">
                <span class="text-gray-400 whitespace-nowrap">{{ $event['at'] instanceof \Carbon\Carbon || $event['at'] instanceof \DateTimeInterface ? $event['at']->format('Y-m-d H:i') : ($event['at'] ?? '') }}</span>
                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $event['label'] ?? 'Event' }}</span>
                <span class="text-gray-500">{{ $event['detail'] ?? '' }}</span>
                @if(!empty($event['url']))
                    <a href="{{ $event['url'] }}" class="text-primary-600 dark:text-primary-400 hover:underline">Open</a>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-500">No recorded events yet.</p>
        @endforelse
    </div>

    {{-- Communication: metadata only; bodies stay behind message authorization --}}
    <div x-show="tab === 'communication'" class="mt-4">
        <h3 class="font-bold mb-2 text-gray-900 dark:text-white">Recent messages (metadata only)</h3>
        @forelse($messages as $m)
            <div class="text-sm border-t border-gray-200 dark:border-gray-700 py-1 text-gray-600 dark:text-gray-300">
                {{ optional($m->created_at)->format('Y-m-d H:i') }} · {{ (int) $m->sender_id === (int) $user->id ? 'sent' : 'received' }} · {{ $m->is_internal ? 'internal staff note (never customer-visible)' : 'visible' }} ·
                <a href="{{ route('admin.messages.show', $m->id) }}" class="text-primary-600 dark:text-primary-400 hover:underline">Open with authorization</a>
            </div>
        @empty
            <p class="text-sm text-gray-500">None.</p>
        @endforelse
    </div>

    {{-- Financial: finance-role only, authoritative rows with settled (not fabricated) states --}}
    @if($showFinance)
        <div x-show="tab === 'financial'" class="mt-4">
            <h3 class="font-bold mb-2 text-gray-900 dark:text-white">Financial (authorized finance view)</h3>
            <p class="text-sm font-semibold mt-2 text-gray-700 dark:text-gray-300">Salary records</p>
            @forelse($salaries as $s)
                <div class="text-sm border-t border-gray-200 dark:border-gray-700 py-1 text-gray-600 dark:text-gray-300">
                    #{{ $s->id }} · {{ $s->period }} · net {{ $s->currency ?? '' }} {{ $s->net_salary }} · <span class="font-medium">{{ $s->status }}</span>
                    · <a href="{{ route('admin.salaries.show', $s->id) }}" class="text-primary-600 dark:text-primary-400 hover:underline">Open</a>
                </div>
            @empty<p class="text-sm text-gray-500">None.</p>@endforelse
            <p class="text-sm font-semibold mt-3 text-gray-700 dark:text-gray-300">Bank transfers</p>
            @forelse($transfers as $t)
                <div class="text-sm border-t border-gray-200 dark:border-gray-700 py-1 text-gray-600 dark:text-gray-300">
                    {{ $t->reference }} · {{ $t->currency }} {{ $t->amount }} · {{ $t->purpose }} · <span class="font-medium">{{ $t->status }}</span> · provider ref: {{ $t->external_reference ?? 'pending confirmation' }}
                </div>
            @empty<p class="text-sm text-gray-500">None.</p>@endforelse
            <p class="text-sm font-semibold mt-3 text-gray-700 dark:text-gray-300">Commissions</p>
            @forelse($commissions as $c)
                <div class="text-sm border-t border-gray-200 dark:border-gray-700 py-1 text-gray-600 dark:text-gray-300">{{ $c->commission_number }} · {{ $c->commission_amount }} · {{ $c->status }}/{{ $c->payment_status }}</div>
            @empty<p class="text-sm text-gray-500">None.</p>@endforelse
            @if(isset($payouts) && $payouts->isNotEmpty())
                <p class="text-sm font-semibold mt-3 text-gray-700 dark:text-gray-300">Commission payouts</p>
                @foreach($payouts as $p)
                    <div class="text-sm border-t border-gray-200 dark:border-gray-700 py-1 text-gray-600 dark:text-gray-300">{{ $p->payout_number ?? ('Payout #' . $p->id) }} · {{ $p->amount }} · {{ $p->status }}</div>
                @endforeach
            @endif
        </div>
    @endif
</div>
@endsection
