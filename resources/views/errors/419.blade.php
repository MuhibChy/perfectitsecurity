@extends('layouts.public')

@section('title', '419 — Session Expired | TechSupport Solutions')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-6 py-20 relative overflow-hidden bg-navy-950 text-white">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#1f293715_1px,transparent_1px),linear-gradient(to_bottom,#1f293715_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)] pointer-events-none"></div>

    <div class="glass-card max-w-xl w-full p-8 lg:p-12 text-center relative z-10 border border-white/10 shadow-2xl backdrop-blur-2xl">
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-primary-500/10 border border-primary-500/30 flex items-center justify-center shadow-[0_0_30px_rgba(79,70,229,0.2)]">
            <svg class="w-10 h-10 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-medium bg-white/5 border border-white/10 text-primary-400 mb-4">
            HTTP 419: CSRF_TOKEN_EXPIRED
        </div>

        <h1 class="text-4xl font-black tracking-tight text-white mb-3">Security Session Expired</h1>
        <p class="text-surface-400 text-sm lg:text-base leading-relaxed mb-8">
            Your CSRF security verification token has timed out due to inactivity. Please refresh the page or resubmit your request.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <button onclick="window.location.reload()" class="btn-primary w-full sm:w-auto px-6 py-3 text-sm">
                Refresh & Try Again
            </button>
            <a href="{{ url('/') }}" class="btn-secondary w-full sm:w-auto px-6 py-3 text-sm">
                Return Home
            </a>
        </div>
    </div>
</div>
@endsection
