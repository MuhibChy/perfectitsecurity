@extends('layouts.app')
@section('page-title', 'Session ' . $session->session_number)

@section('content')
<div class="space-y-6">
    <x-page-header title="Session {{ $session->session_number }}" sys="OPS://FIELD/REMOTE/SHOW" />
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="term-panel p-6 lg:col-span-2">
            <div class="flex flex-wrap gap-2 mb-4"><x-status-badge :status="$session->status" /><span class="text-sm opacity-70">{{ ucfirst(str_replace('_', ' ', $session->provider)) }}</span></div>
            <dl class="grid md:grid-cols-2 gap-2 text-sm">
                <div><dt class="opacity-60">Customer</dt><dd>{{ $session->customer->name ?? '-' }}</dd></div>
                <div><dt class="opacity-60">Technician</dt><dd>{{ $session->technician->name ?? '-' }}</dd></div>
                <div><dt class="opacity-60">Ticket</dt><dd class="font-mono">{{ $session->ticket->ticket_number ?? '-' }}</dd></div>
                <div><dt class="opacity-60">Consent</dt><dd>{{ $session->consent_given ? 'Given ' . $session->consent_at?->format('Y-m-d H:i') . ' by ' . ($session->consenter->name ?? 'customer') : 'Not given' }}</dd></div>
                <div><dt class="opacity-60">Scheduled</dt><dd>{{ $session->scheduled_at?->format('Y-m-d H:i') ?? '-' }}</dd></div>
                <div><dt class="opacity-60">Join link</dt><dd>{{ $session->session_url ? 'Recorded (see customer portal)' : '-' }}</dd></div>
            </dl>
            @if($session->outcome)<h3 class="font-semibold mt-4">Outcome</h3><p class="whitespace-pre-line">{{ $session->outcome }}</p>@endif
        </div>
        <div class="term-panel p-6 h-fit">
            <h3 class="font-semibold mb-3">Manage</h3>
            <form method="POST" action="{{ route('admin.remote.update', $session) }}" class="space-y-3">@csrf @method('PATCH')
                <select name="technician_id" class="term-input w-full"><option value="">No technician</option>@foreach($technicians as $t)<option value="{{ $t->id }}" {{ $session->technician_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach</select>
                <input type="url" name="session_url" value="{{ $session->session_url }}" placeholder="Join link" class="term-input w-full">
                <input type="datetime-local" name="scheduled_at" value="{{ $session->scheduled_at?->format('Y-m-d\TH:i') }}" class="term-input w-full">
                <select name="status" class="term-input w-full">@foreach(['requested','scheduled','active','completed','expired','cancelled'] as $s)<option value="{{ $s }}" {{ $session->status == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>@endforeach</select>
                <textarea name="outcome" rows="3" placeholder="Session outcome" class="term-input w-full">{{ $session->outcome }}</textarea>
                <button class="term-btn term-btn-sm w-full">Save</button>
            </form>
            <p class="text-xs mt-3 opacity-70">Sessions require recorded customer consent and an assigned technician before going active.</p>
        </div>
    </div>
</div>
@endsection
