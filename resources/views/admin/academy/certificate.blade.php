@extends('layouts.app')
@section('title', 'Training Completion Record')
@section('page-title', 'Completion Record')

@section('content')

    <x-page-header title="Completion Record" sys="CONTENT://ACADEMY" />
<div class="max-w-3xl mx-auto">
    <div class="term-panel p-10 text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1.5" style="background: linear-gradient(90deg,#16A34A,#2563EB);"></div>
        <p class="label mb-2">PerfectITSecurity Academy · Internal Training Record</p>
        <h2 class="heading-lg mb-1">{{ $certificate->course->title }}</h2>
        <p class="body-md mb-6">This record confirms internal training completion (not an external professional certification).</p>
        <div class="text-2xl font-bold text-slate-900 dark:text-white mb-1">{{ $certificate->user->name }}</div>
        <p class="text-sm text-slate-500 mb-6">{{ $certificate->user->role ? ucfirst(str_replace('_',' ',$certificate->user->role)) : '' }} · {{ $certificate->user->email }}</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center mb-6">
            <div><div class="font-bold">{{ $certificate->score ?? '—' }}{{ $certificate->score !== null ? '%' : '' }}</div><div class="text-xs text-slate-500">Score</div></div>
            <div><div class="font-bold">{{ $certificate->completed_at?->format('M d, Y') }}</div><div class="text-xs text-slate-500">Completed</div></div>
            <div><div class="font-bold">v{{ $certificate->course_version }}</div><div class="text-xs text-slate-500">Course version</div></div>
            <div><div class="font-bold font-mono text-sm">{{ $certificate->certificate_no }}</div><div class="text-xs text-slate-500">Record no.</div></div>
        </div>
        <p class="text-xs text-slate-500">Status: Completed · Verify against Academy reports.</p>
    </div>
    <div class="text-center mt-4 no-print"><button onclick="window.print()" class="term-btn term-btn-ghost term-btn-sm">Print record</button></div>
</div>
@endsection
