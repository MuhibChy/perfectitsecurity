@extends('layouts.app')
@section('page-title', $member->name)
@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-page-header :title="$member->name" :subtitle="($member->job_title ?? $member->roleDisplayName()) . ($member->department ? ' · ' . $member->department : '')" sys="ADMIN://CONTACT" :breadcrumbs="['Directory' => route('admin.directory.index'), $member->name => null]" />

    <div class="term-panel p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
            <img src="{{ $member->avatar_url }}" alt="Profile photo of {{ $member->name }}" class="w-20 h-20 rounded-full object-cover border border-slate-200 dark:border-white/10">
            <div class="min-w-0">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $member->name }}</h2>
                <p class="text-sm text-slate-600 dark:text-term-800">{{ $member->member_number }} · {{ $member->roleDisplayName() }}</p>
                @if($card['presence'])
                <p class="mt-1 font-mono text-[11px] tracking-[0.14em] uppercase text-slate-500 dark:text-term-700">Status: {{ $card['presence'] }}</p>
                @endif
            </div>
            <div class="sm:ml-auto flex flex-wrap gap-2.5">
                <a href="{{ route('admin.messages.create') }}?to={{ $member->id }}" class="term-btn term-btn-sm">Message</a>
                @if(auth()->user()->isFinanceManager() && !$member->isCustomer())
                <a href="{{ route('admin.ecosystem.compensation.edit', $member) }}" class="term-btn term-btn-sm term-btn-ghost">Compensation</a>
                @endif
            </div>
        </div>

        <dl class="mt-6 grid sm:grid-cols-2 gap-4 text-sm">
            <div class="term-panel-2 p-3">
                <dt class="term-field-label !mb-1">Email</dt>
                <dd class="text-slate-900 dark:text-white break-all">{{ $contact['email'] }}</dd>
            </div>
            <div class="term-panel-2 p-3">
                <dt class="term-field-label !mb-1">Phone / WhatsApp</dt>
                <dd class="text-slate-900 dark:text-white">{{ $contact['phone'] ?? '—' }}{{ $contact['whatsapp'] ? ' · WA ' . $contact['whatsapp'] : '' }}</dd>
            </div>
            <div class="term-panel-2 p-3">
                <dt class="term-field-label !mb-1">Preferred contact</dt>
                <dd class="text-slate-900 dark:text-white">{{ $card['preferred_contact_method'] ? ucfirst($card['preferred_contact_method']) : '—' }}{{ $card['contact_hours'] ? ' · ' . $card['contact_hours'] : '' }}</dd>
            </div>
            @if($compensationLabel)
            <div class="term-panel-2 p-3">
                <dt class="term-field-label !mb-1">Compensation model (finance only)</dt>
                <dd class="text-slate-900 dark:text-white">{{ $compensationLabel }}</dd>
            </div>
            @endif
        </dl>

        @if(!empty($card['skills']))
        <div class="mt-4">
            <h3 class="term-field-label">Skills</h3>
            <div class="flex flex-wrap gap-1.5">
                @foreach($card['skills'] as $skill)
                <span class="term-tag">{{ $skill }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @if($assignments->isNotEmpty())
        <div class="mt-6">
            <h3 class="term-field-label">Active assignments</h3>
            <ul class="space-y-1.5 text-sm text-slate-600 dark:text-term-800">
                @foreach($assignments as $a)
                <li>{{ class_basename($a->assignable_type) }} #{{ $a->assignable_id }} <span class="font-mono text-[11px]">· since {{ optional($a->started_at)->format('Y-m-d') }}</span></li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection
