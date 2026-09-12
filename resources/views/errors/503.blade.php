@extends('layouts.public')

@section('title', '503 — Maintenance in Progress | TechSupport Solutions')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-6 py-20 relative overflow-hidden bg-navy-950 text-white">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#1f293715_1px,transparent_1px),linear-gradient(to_bottom,#1f293715_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="glass-card max-w-xl w-full p-8 lg:p-12 text-center relative z-10 border border-white/10 shadow-2xl backdrop-blur-2xl">
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center shadow-[0_0_30px_rgba(6,182,212,0.2)]">
            <svg class="w-10 h-10 text-cyan-400 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-medium bg-white/5 border border-white/10 text-cyan-400 mb-4">
            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
            HTTP 503: SCHEDULED_MAINTENANCE
        </div>

        <h1 class="text-4xl lg:text-5xl font-black tracking-tight text-white mb-3">System Upgrades Active</h1>
        <p class="text-surface-400 text-sm lg:text-base leading-relaxed mb-8">
            Our cloud infrastructure is currently undergoing scheduled maintenance and security hardening. Services will resume shortly.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <button onclick="window.location.reload()" class="btn-primary w-full sm:w-auto px-6 py-3 text-sm">
                Check Status
            </button>
            <a href="{{ route('contact') }}" class="btn-secondary w-full sm:w-auto px-6 py-3 text-sm">
                Urgent Operations Inquiry
            </a>
        </div>
    </div>
</div>
@endsection
