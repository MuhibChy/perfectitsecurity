@extends('layouts.public')

@section('title', '403 — Access Forbidden | TechSupport Solutions')
@section('description', 'Access to this resource is restricted by role-based access controls.')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-6 py-20 relative overflow-hidden bg-navy-950 text-white">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#1f293715_1px,transparent_1px),linear-gradient(to_bottom,#1f293715_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="glass-card max-w-xl w-full p-8 lg:p-12 text-center relative z-10 border border-white/10 shadow-2xl backdrop-blur-2xl">
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center shadow-[0_0_30px_rgba(245,158,11,0.2)]">
            <svg class="w-10 h-10 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-medium bg-white/5 border border-white/10 text-amber-400 mb-4">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            HTTP 403: ACCESS_DENIED_RBAC
        </div>

        <h1 class="text-4xl lg:text-5xl font-black tracking-tight text-white mb-3">Access Restricted</h1>
        <p class="text-surface-400 text-sm lg:text-base leading-relaxed mb-8">
            You do not possess the required security privileges or role elevation to interact with this resource. Contact your workspace administrator to request access.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="btn-primary w-full sm:w-auto px-6 py-3 text-sm">
                Return to Safety
            </a>
            <a href="{{ route('login') }}" class="btn-secondary w-full sm:w-auto px-6 py-3 text-sm">
                Switch Account
            </a>
            <a href="{{ route('contact') }}" class="btn-ghost w-full sm:w-auto px-6 py-3 text-sm text-surface-400 hover:text-white">
                Request Clearance &rarr;
            </a>
        </div>
    </div>
</div>
@endsection
