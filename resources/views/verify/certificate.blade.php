<!DOCTYPE html>
<html lang="en" class="dark"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#050807"><title>Certificate Verification — PerfectITSecurity</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="bg-term-0 text-term-950 min-h-screen flex items-center justify-center p-4 sm:p-6 relative">
<x-terminal-background />
<x-global-3d-scene />
<x-global-hud-frame />
<div class="w-full max-w-md auth-term p-8 text-center relative z-10">
<span class="auth-term-corner tl" aria-hidden="true"></span>
<span class="auth-term-corner br" aria-hidden="true"></span>
<div class="font-mono text-[10px] tracking-[0.28em] text-accent-soft uppercase mb-3">VERIFY://CERTIFICATE</div>
<h1 class="font-display text-lg font-extrabold tracking-tight text-white">PERFECT<span class="text-accent">IT</span>SECURITY</h1>
<p class="font-mono text-[11px] text-term-700 tracking-[0.2em] uppercase mt-1 mb-6">CERTIFICATE VERIFICATION</p>
@if($valid)
<div class="mb-4"><span class="term-tag" style="color:#5CEFA8;border-color:rgba(0,230,122,0.4);background:rgba(0,230,122,0.08);text-transform:uppercase">● Certificate Valid</span></div>
<div class="text-left text-sm space-y-2 mt-2 text-term-800">
<p><span class="font-mono text-[11px] text-term-700 uppercase tracking-wider">Learner:</span> <strong class="text-term-950">{{ $certificate->user->name }}</strong></p>
<p><span class="font-mono text-[11px] text-term-700 uppercase tracking-wider">Course:</span> {{ $certificate->course->title ?? ('Course #' . $certificate->course_id) }}</p>
<p><span class="font-mono text-[11px] text-term-700 uppercase tracking-wider">Certificate:</span> <strong class="font-mono text-term-950">{{ $certificate->certificate_no }}</strong></p>
<p><span class="font-mono text-[11px] text-term-700 uppercase tracking-wider">Completed:</span> {{ optional($certificate->completed_at)->format('Y-m-d') }}</p>
<p><span class="font-mono text-[11px] text-term-700 uppercase tracking-wider">Status:</span> ACTIVE</p>
</div>
@else
<div class="mb-4"><span class="term-tag" style="color:#FF8A8A;border-color:rgba(255,92,92,0.4);background:rgba(255,92,92,0.08);text-transform:uppercase">● Certificate Not Valid</span></div>
<p class="text-sm text-term-800">This link does not correspond to an active certificate.</p>
@endif
</div></body></html>
