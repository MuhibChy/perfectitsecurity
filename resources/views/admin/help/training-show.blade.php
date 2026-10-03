@extends('layouts.app')
@section('title', $lesson['title'] . ' — Training Center')
@section('page-title', $lesson['title'])

@section('content')
<x-page-header sys="CONTENT://HELP" :title="$lesson['title']" :subtitle="$lesson['summary']" :breadcrumbs="['Training' => route('admin.help.training.index'), $lesson['title'] => null]">
    <button onclick="window.print()" class="term-btn term-btn-ghost term-btn-sm no-print">Print Training</button>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-[220px_1fr] gap-6 items-start">
    <aside class="card p-4 lg:sticky lg:top-20 no-print hidden lg:block">
        <h2 class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-2">On This Page</h2>
        <nav class="space-y-1 text-sm">
            @foreach(['overview' => 'Overview', 'who-when' => 'Who & When', 'steps' => 'Steps', 'example' => 'Example', 'expected' => 'Expected Result', 'mistakes' => 'Common Mistakes', 'problems' => 'Problems & Solutions', 'next' => 'Next Step'] as $id => $label)
            <a href="#{{ $id }}" class="block px-2 py-1 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5">{{ $label }}</a>
            @endforeach
        </nav>
    </aside>

    <article class="space-y-4 min-w-0">
        <section id="overview" class="card p-6 scroll-mt-24">
            <h2 class="heading-sm mb-2">1. What is this?</h2>
            <p class="text-[15px] leading-relaxed text-slate-700 dark:text-slate-200">{{ $lesson['what'] }}</p>
            <h2 class="heading-sm mt-4 mb-2">2. Why is it important?</h2>
            <p class="text-[15px] leading-relaxed text-slate-700 dark:text-slate-200">{{ $lesson['why'] }}</p>
        </section>

        <section id="who-when" class="card p-6 scroll-mt-24">
            <h2 class="heading-sm mb-2">3. Who uses it?</h2>
            <p class="text-[15px] text-slate-700 dark:text-slate-200 mb-3">{{ $lesson['who'] }}</p>
            <h2 class="heading-sm mb-2">4. When should it be used?</h2>
            <p class="text-[15px] text-slate-700 dark:text-slate-200">{{ $lesson['when'] }}</p>
        </section>

        <section id="steps" class="card p-6 scroll-mt-24">
            <h2 class="heading-sm mb-3">5. Step-by-step procedure</h2>
            <ol class="space-y-2.5">
                @foreach($lesson['steps'] as $i => $step)
                <li class="flex gap-3 text-[15px]"><span class="w-7 h-7 rounded-lg flex-shrink-0 flex items-center justify-center font-bold text-white text-xs" style="background: linear-gradient(135deg,#16A34A,#2563EB);">{{ $i + 1 }}</span><span class="text-slate-700 dark:text-slate-200">{{ $step }}</span></li>
                @endforeach
            </ol>
        </section>

        <section id="example" class="term-panel p-6 scroll-mt-24">
            <h2 class="heading-sm mb-2">6. Real-world example <span class="term-tag ml-1">[TRAINING]</span></h2>
            <p class="text-[15px] leading-relaxed text-slate-700 dark:text-slate-200">{{ $lesson['example'] }}</p>
        </section>

        <section id="expected" class="card p-6 scroll-mt-24">
            <h2 class="heading-sm mb-2">7. Expected result</h2>
            <p class="text-[15px] text-slate-700 dark:text-slate-200">{{ $lesson['expected'] }}</p>
        </section>

        <section id="mistakes" class="card p-6 scroll-mt-24 border-l-4 border-l-amber-500">
            <h2 class="heading-sm mb-2">8. Common mistakes</h2>
            <ul class="space-y-1.5 text-[15px] text-slate-700 dark:text-slate-200">@foreach($lesson['mistakes'] as $m)<li> {{ $m }}</li>@endforeach</ul>
        </section>

        <section id="problems" class="card p-6 scroll-mt-24">
            <h2 class="heading-sm mb-2">9. Related problems & solutions</h2>
            @forelse($relatedProblems as $p)
            <a href="{{ route('admin.help.problems.show', $p['slug']) }}" class="block py-2 border-b border-slate-100 dark:border-white/5 last:border-0 link-arrow text-sm">{{ $p['title'] }} →</a>
            @empty
            <p class="body-sm">No linked troubleshooting for this guide yet — see the <a href="{{ route('admin.help.problems.index') }}" class="link-arrow text-sm">Problem & Solution Center →</a></p>
            @endforelse
        </section>

        <section id="next" class="card p-6 scroll-mt-24">
            <h2 class="heading-sm mb-3">10. Next step</h2>
            <div class="flex flex-wrap gap-2 no-print">
                @if($prev)<a href="{{ route('admin.help.training.show', $prev['slug']) }}" class="term-btn term-btn-ghost term-btn-sm">← {{ $prev['title'] }}</a>@endif
                <a href="{{ route('admin.help.training.index') }}" class="term-btn term-btn-ghost term-btn-sm">Training Home</a>
                @if($next)<a href="{{ route('admin.help.training.show', $next['slug']) }}" class="term-btn term-btn-sm">{{ $next['title'] }} →</a>@endif
            </div>
            <p class="text-xs text-slate-500 mt-4">Training Version 1.0 · Last Updated 17 September 2026 · Lesson {{ ($pos ?? 0) + 1 }} of {{ count($ordered) }}</p>
        </section>
    </article>
</div>
@endsection
