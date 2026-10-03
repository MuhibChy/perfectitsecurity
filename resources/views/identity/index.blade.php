@extends('layouts.app')
@section('page-title', 'Identity Verification')
@section('content')
<div class="space-y-6">
<x-page-header title="Identity Verification" :subtitle="'Member ' . $user->member_number . ' · Status: ' . strtoupper($user->identity_status)" sys="IDENTITY://VERIFY" num="15" />
<div class="term-panel p-6 mb-4">
    @if(session('success'))<div class="term-alert term-alert-ok mb-4"><span class="term-alert-tag">OK</span><span>{{ session('success') }}</span></div>@endif
    @if(in_array($user->identity_status, ['not_started', 'resubmission_required', 'rejected', 'expired', 'revoked'], true))
        <form method="POST" action="{{ route('identity.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4 max-w-lg">
            @csrf
            <div>
                <label class="term-field-label">Document type</label>
                <select name="document_type" required class="term-input">
                    @foreach($types as $t)<option value="{{ $t }}">{{ ucwords(str_replace('_', ' ', $t)) }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="term-field-label">Issuing country (ISO, optional)</label>
                <input type="text" name="issuing_country" maxlength="3" placeholder="GBR" class="term-input">
            </div>
            <div>
                <label class="term-field-label">Document file (JPG/PNG/WebP/PDF, max 8MB)</label>
                <input type="file" name="document" required accept=".jpg,.jpeg,.png,.webp,.pdf" class="term-input">
                @error('document')<p class="term-error">{{ $message }}</p>@enderror
            </div>
            <button class="term-btn term-btn-sm">Submit for review</button>
            <p class="term-hint">Uploading never auto-verifies. An authorized reviewer approves or rejects with a reason; every step is audited.</p>
        </form>
    @else
        <p class="text-sm text-slate-600 dark:text-term-800 mt-3">Your case is {{ $user->identity_status }}. No action needed{{ $user->identity_status === 'verified' ? ' — verification completed ' . optional($user->identity_verified_at)->format('Y-m-d') : '' }}.</p>
    @endif
</div>
<div class="term-panel p-6">
    <h3 class="font-bold mb-2 text-slate-900 dark:text-white">Submission history (preserved)</h3>
    @forelse($documents as $d)
        <div class="text-sm border-t border-white/5 py-2 text-slate-600 dark:text-term-800">
            #{{ $d->id }} · {{ ucwords(str_replace('_', ' ', $d->document_type)) }} · <x-status-badge :status="$d->status" /> · submitted {{ $d->created_at->format('Y-m-d H:i') }}
            @if($d->rejection_reason)<div class="term-alert term-alert-warn mt-1"><span class="term-alert-tag">NOTE</span><span>Reviewer note: {{ $d->rejection_reason }}</span></div>@endif
            @if((int) $d->user_id === (int) $user->id)<a href="{{ route('identity.download', $d->id) }}" class="term-link !text-[11px] ml-2">Download my copy</a>@endif
        </div>
    @empty<p class="text-sm text-slate-600 dark:text-term-800">No submissions yet.</p>@endforelse
</div>
</div>
@endsection
