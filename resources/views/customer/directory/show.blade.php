@extends('layouts.app')
@section('page-title', $staff->name)
@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-page-header :title="$staff->name" :subtitle="($staff->job_title ?? $staff->roleDisplayName()) . ($staff->department ? ' · ' . $staff->department : '')" sys="CLIENT://CONTACT" :breadcrumbs="['Directory' => route('portal.directory.index'), $staff->name => null]" />

    <div class="term-panel p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
            <img src="{{ $staff->avatar_url }}" alt="Profile photo of {{ $staff->name }}" class="w-20 h-20 rounded-full object-cover border border-slate-200 dark:border-white/10">
            <div class="min-w-0">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $staff->name }}</h2>
                <p class="text-sm text-slate-600 dark:text-term-800">{{ $staff->job_title ?? $staff->roleDisplayName() }}</p>
                @if($card['presence'])
                <p class="mt-1 font-mono text-[11px] tracking-[0.14em] uppercase text-slate-500 dark:text-term-700">Status: {{ $card['presence'] }}</p>
                @endif
            </div>
            <div class="sm:ml-auto flex flex-wrap gap-2.5">
                <a href="{{ route('portal.messages.create') }}?to={{ $staff->id }}" class="term-btn term-btn-sm">Message</a>
            </div>
        </div>

        <dl class="mt-6 grid sm:grid-cols-2 gap-4 text-sm">
            <div class="term-panel-2 p-3">
                <dt class="term-field-label !mb-1">Preferred contact</dt>
                <dd class="text-slate-900 dark:text-white">{{ $card['preferred_contact_method'] ? ucfirst($card['preferred_contact_method']) : '—' }}</dd>
            </div>
            <div class="term-panel-2 p-3">
                <dt class="term-field-label !mb-1">Available hours</dt>
                <dd class="text-slate-900 dark:text-white">{{ $card['contact_hours'] ?? '—' }}</dd>
            </div>
        </dl>

        @if(!empty($card['skills']))
        <div class="mt-4">
            <h3 class="term-field-label">Areas of expertise</h3>
            <div class="flex flex-wrap gap-1.5">
                @foreach($card['skills'] as $skill)
                <span class="term-tag">{{ $skill }}</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Direct contact unlocks with a working relationship (§11, §18) --}}
        @if($contact)
        <div class="mt-6 pt-5 border-t border-slate-200 dark:border-white/10">
            <h3 class="term-field-label">Direct contact</h3>
            <ul class="space-y-1.5 text-sm text-slate-600 dark:text-term-800">
                @if($contact['email'])<li>Email: <span class="text-slate-900 dark:text-white">{{ $contact['email'] }}</span></li>@endif
                @if($contact['phone'])<li>Phone: <span class="text-slate-900 dark:text-white">{{ $contact['phone'] }}</span></li>@endif
                @if($contact['whatsapp'])<li>WhatsApp: <span class="text-slate-900 dark:text-white">{{ $contact['whatsapp'] }}</span></li>@endif
            </ul>
        </div>
        @else
        <p class="mt-6 pt-5 border-t border-slate-200 dark:border-white/10 term-hint">Direct numbers appear here once an assignment, ticket, project or order links you with {{ $staff->name }}.</p>
        @endif
    </div>
</div>
@endsection
