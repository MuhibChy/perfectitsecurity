@extends('layouts.app')
@section('page-title', 'My Digital ID')
@section('content')
<div class="space-y-6">
<x-page-header title="Digital Member ID" subtitle="Your verifiable membership credential." sys="IDENTITY://ID" />
<div class="term-panel p-6 mb-4 max-w-xl mx-auto">
    <h2 class="text-xl font-bold text-slate-900 dark:text-white text-center tracking-widest font-mono">PERFECTITSECURITY</h2>
    <p class="term-hint text-center mb-4">DIGITAL MEMBER ID</p>
    @if(session('success'))<div class="term-alert term-alert-ok mb-3"><span class="term-alert-tag">OK</span><span>{{ session('success') }}</span></div>@endif
    @if($card)
        <div class="term-panel-2 p-5 flex gap-5 items-center">
            <img src="{{ $user->avatar_url }}" alt="photo" class="w-24 h-24 object-cover border border-white/10">
            <div class="text-sm space-y-1 text-slate-600 dark:text-term-800">
                <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $user->name }}</p>
                <p>Member ID: <strong class="font-mono">{{ $user->member_number }}</strong></p>
                <p>Role: {{ $user->roleDisplayName() }}</p>
                <p>Status: <strong>{{ ($user->is_active ?? true) ? 'ACTIVE' : 'INACTIVE' }}</strong> · Identity: <strong>{{ strtoupper($user->identity_status) }}</strong> · 2FA: <strong>{{ $user->hasMfaEnabled() ? 'ENABLED' : 'NOT ENABLED' }}</strong></p>
                <p>Card: <strong>{{ strtoupper($card->status) }}</strong> · Issued: {{ optional($card->issued_at)->format('Y-m-d') }}</p>
            </div>
        </div>
        @if($qrUrl)
            <div class="flex justify-center mt-4"><img src="{{ $qrUrl }}" alt="verification QR" class="w-40 h-40 bg-white p-2"></div>
            <p class="term-hint text-center mt-2">Scan to verify — reveals name, member ID, role and statuses only.</p>
        @endif
        <div class="flex flex-wrap gap-2 justify-center mt-4">
            <a href="{{ route('idcard.pdf') }}" class="term-btn term-btn-sm">Download PDF</a>
            <form method="POST" action="{{ route('idcard.issue') }}">@csrf<button class="term-btn term-btn-ghost term-btn-sm">Replace card</button></form>
        </div>
    @else
        <p class="text-sm text-slate-600 dark:text-term-800 text-center">No card issued yet.</p>
        <form method="POST" action="{{ route('idcard.issue') }}" class="text-center mt-3">@csrf<button class="term-btn term-btn-sm">Issue my ID card</button></form>
    @endif
</div>
</div>
@endsection
