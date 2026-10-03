@extends('layouts.app')
@section('title', $article['title'] . ' — Problem & Solution')
@section('page-title', $article['title'])

@section('content')
<x-page-header sys="CONTENT://HELP" :title="$article['title']" :subtitle="($categories[$article['category']] ?? $article['category']) . ' · Troubleshooting article'" :breadcrumbs="['Problem & Solution' => route('admin.help.problems.index'), $article['title'] => null]">
    <button onclick="window.print()" class="term-btn term-btn-ghost term-btn-sm no-print">Print Article</button>
</x-page-header>

<article class="max-w-4xl space-y-4">
    <section class="card p-6 border-l-4 border-l-rose-500">
        <h2 class="heading-sm mb-2">Problem</h2>
        <p class="text-[15px] text-slate-700 dark:text-slate-200">{{ $article['title'] }}.</p>
        <h3 class="font-semibold text-sm mt-3 mb-1">Symptoms</h3>
        <ul class="list-disc list-inside text-[15px] text-slate-700 dark:text-slate-200 space-y-1">@foreach($article['symptoms'] as $s)<li>{{ $s }}</li>@endforeach</ul>
    </section>

    <section class="card p-6">
        <h2 class="heading-sm mb-2">Possible Causes</h2>
        <ol class="list-decimal list-inside text-[15px] text-slate-700 dark:text-slate-200 space-y-1">@foreach($article['causes'] as $i => $cause)<li>{{ $cause }}</li>@endforeach</ol>
    </section>

    <section class="card p-6">
        <h2 class="heading-sm mb-3">Solution — safe step-by-step troubleshooting</h2>
        <ol class="space-y-2.5">
            @foreach($article['steps'] as $i => $step)
            <li class="flex gap-3 text-[15px]"><span class="w-7 h-7 rounded-lg flex-shrink-0 flex items-center justify-center font-bold text-white text-xs" style="background: linear-gradient(135deg,#16A34A,#2563EB);">{{ $i + 1 }}</span><span class="text-slate-700 dark:text-slate-200">{{ $step }}</span></li>
            @endforeach
        </ol>
    </section>

    <section class="card p-6 border-l-4 border-l-emerald-500">
        <h2 class="heading-sm mb-2">Verification</h2>
        <p class="text-[15px] text-slate-700 dark:text-slate-200">{{ $article['verification'] }}</p>
    </section>

    <section class="card p-6 border-l-4 border-l-amber-500">
        <h2 class="heading-sm mb-2">If It Still Does Not Work — Escalation</h2>
        <p class="text-[15px] text-slate-700 dark:text-slate-200">{{ $article['escalation'] }}</p>
    </section>

    <section class="card p-6">
        <h2 class="heading-sm mb-2">Related Training</h2>
        @forelse($relatedLessons as $lesson)
        <a href="{{ route('admin.help.training.show', $lesson['slug']) }}" class="block py-2 border-b border-slate-100 dark:border-white/5 last:border-0 link-arrow text-sm">{{ $lesson['title'] }} →</a>
        @empty
        <p class="body-sm">See the <a href="{{ route('admin.help.training.index') }}" class="link-arrow text-sm">Training Center →</a></p>
        @endforelse
        <div class="flex flex-wrap gap-2 mt-4 no-print">
            <a href="{{ route('admin.help.problems.index') }}" class="term-btn term-btn-ghost term-btn-sm">All Problems</a>
            <a href="{{ route('admin.help.training.index') }}" class="term-btn term-btn-ghost term-btn-sm">Training Center</a>
        </div>
        <p class="text-xs text-slate-500 mt-4">Training Version 1.0 · Last Updated 17 September 2026 · Synthetic examples only</p>
    </section>
</article>
@endsection
