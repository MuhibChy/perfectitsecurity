@extends('layouts.app')
@section('page-title', 'Call Logs')
@section('content')
<div class="space-y-6">
    <x-page-header title="Call Logs" subtitle="Voice-communication records on the manual rail. A telephony vendor plugs in via the CallProvider contract — no vendor is hard-coded." sys="ADMIN://CALLS" />

    <form method="POST" action="{{ route('admin.ecosystem.calls.store') }}" class="term-panel p-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        @csrf
        <div>
            <label class="term-field-label">Recipient (user ID)</label>
            <input type="number" name="recipient_id" class="term-input" required min="1">
        </div>
        <div>
            <label class="term-field-label">Outcome</label>
            <select name="outcome" class="term-input" required>
                @foreach(['completed', 'missed', 'failed', 'voicemail', 'scheduled'] as $o)
                <option value="{{ $o }}">{{ ucfirst($o) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="term-field-label">Duration (seconds)</label>
            <input type="number" name="duration_seconds" class="term-input" min="0" max="86400" value="0">
        </div>
        <div>
            <label class="term-field-label">Started at</label>
            <input type="datetime-local" name="started_at" class="term-input">
        </div>
        <div>
            <label class="term-field-label">Link type</label>
            <select name="related_type" class="term-input">
                <option value="">— None —</option>
                <option value="ticket">Ticket</option>
                <option value="project">Project</option>
                <option value="order">Order</option>
            </select>
        </div>
        <div>
            <label class="term-field-label">Linked record ID</label>
            <input type="number" name="related_id" class="term-input" min="1">
        </div>
        <div class="sm:col-span-2 lg:col-span-2">
            <label class="term-field-label">Subject / notes</label>
            <input type="text" name="subject" class="term-input" maxlength="255">
        </div>
        <div class="sm:col-span-2 lg:col-span-4">
            <button class="term-btn term-btn-sm" type="submit">Log call</button>
        </div>
    </form>

    <div class="term-panel p-6">
        <div class="term-table-wrap"><table class="term-table">
            <thead><tr><th>UUID</th><th>Caller → Recipient</th><th>Started</th><th>Duration</th><th>Outcome</th><th>Linked</th></tr></thead>
            <tbody>
                @forelse($calls as $c)
                <tr>
                    <td class="mono-id">{{ mb_substr($c->uuid, 0, 8) }}…</td>
                    <td>{{ $c->caller->name }} → {{ $c->recipient->name }}</td>
                    <td class="mono-id">{{ optional($c->started_at)->format('Y-m-d H:i') }}</td>
                    <td class="mono-id">{{ gmdate('H:i:s', $c->duration_seconds) }}</td>
                    <td><x-status-badge :status="$c->outcome === 'completed' ? 'completed' : ($c->outcome === 'missed' ? 'pending' : 'info')" :label="$c->outcome" /></td>
                    <td class="mono-id">@if($c->related_type){{ class_basename($c->related_type) }} #{{ $c->related_id }}@else—@endif</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-slate-500">No calls logged.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="mt-3">{{ $calls->links() }}</div>
    </div>
</div>
@endsection
