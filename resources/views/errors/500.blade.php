@extends('layouts.public')

@section('title', '500 — Server Incident | TechSupport Solutions')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-6 py-20 relative overflow-hidden bg-navy-950 text-white">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#1f293715_1px,transparent_1px),linear-gradient(to_bottom,#1f293715_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="glass-card max-w-xl w-full p-8 lg:p-12 text-center relative z-10 border border-white/10 shadow-2xl backdrop-blur-2xl">
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-center shadow-[0_0_30px_rgba(244,63,94,0.2)]">
            <svg class="w-10 h-10 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-medium bg-white/5 border border-white/10 text-rose-400 mb-4">
            <span class="w-2 h-2 rounded-full bg-rose-400 animate-ping"></span>
            HTTP 500: INTERNAL_SERVER_INCIDENT
        </div>

        <h1 class="text-4xl lg:text-5xl font-black tracking-tight text-white mb-3">Internal System Error</h1>
        <p class="text-surface-400 text-sm lg:text-base leading-relaxed mb-8">
            An unhandled system exception occurred during execution. Our telemetry watchdog has automatically captured diagnostics and notified the site reliability team.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="btn-primary w-full sm:w-auto px-6 py-3 text-sm">
                Return to Safety
            </a>
            <a href="{{ route('contact') }}" class="btn-secondary w-full sm:w-auto px-6 py-3 text-sm">
                Report Outage
            </a>
        </div>
    </div>
</div>
@endsection
