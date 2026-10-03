@extends('layouts.app')
@section('page-title', 'Remote Support')
@section('content')
<div class="space-y-6">
    <x-page-header title="Remote Support" subtitle="Request remote assistance. Sessions start only with your explicit consent." sys="SUPPORT://REMOTE" />
    <div class="term-panel p-6">
        <h2 class="font-semibold mb-3">Request assistance</h2>
        <form method="POST" action="{{ route('portal.itsm.remote.store') }}" class="flex flex-wrap gap-3">@csrf
            <select name="provider" class="term-input"><option value="support_link">Support link</option><option value="teamviewer">TeamViewer</option><option value="anydesk">AnyDesk</option><option value="other">Other</option></select>
            <input type="datetime-local" name="scheduled_at" class="term-input">
            <button class="term-btn term-btn-sm">Request</button>
        </form>
    </div>
    <div class="term-panel overflow-hidden"><div class="overflow-x-auto term-table-wrap"><table class="data-table term-table">
        <thead><tr><th>Session #</th><th>Provider</th><th>Status</th><th>Scheduled</th><th>Consent</th><th class="text-right">Actions</th></tr></thead>
        <tbody>@forelse($sessions as $s)<tr>
            <td data-label="Session #" class="font-mono">{{ $s->session_number }}</td>
            <td data-label="Provider">{{ ucfirst(str_replace('_', ' ', $s->provider)) }}</td>
            <td data-label="Status"><x-status-badge :status="$s->status" /></td>
            <td data-label="Scheduled">{{ $s->scheduled_at?->format('Y-m-d H:i') ?? '-' }}</td>
            <td data-label="Consent">{{ $s->consent_given ? 'Given' : 'Not given' }}</td>
            <td class="text-right" data-label="Actions">@if(!$s->consent_given && in_array($s->status, ['requested','scheduled']))
                <form method="POST" action="{{ route('portal.itsm.remote.consent', $s) }}">@csrf<button class="term-btn term-btn-sm">Give consent</button></form>
                @else<span class="text-sm opacity-60">—</span>@endif</td>
        </tr>@empty<tr><td colspan="6" class="text-center py-6">No remote sessions.</td></tr>@endforelse</tbody>
    </table></div><div class="p-4">{{ $sessions->links() }}</div></div>
</div>
@endsection
