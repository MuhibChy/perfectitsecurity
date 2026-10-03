@extends('layouts.public')

@section('title', 'Resources — PerfectITSecurity')
@section('description', 'Curated cybersecurity tools, threat intelligence, and IT resources.')

@section('content')

{{-- HERO — KNOWLEDGE://RESOURCES --}}
<section class="relative w-full overflow-hidden" aria-labelledby="links-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-2xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">KNOWLEDGE://RESOURCES</span>
            </div>
            <h1 id="links-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance">
                CYBERSECURITY TOOLS <span class="text-accent-soft">AND RESOURCES.</span>
            </h1>
            <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-600 dark:text-term-800">A curated collection of threat intelligence, security tools, and IT resources for professionals.</p>
        </div>
    </div>
</section>

{{-- Links grid --}}
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-label="Curated resources">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @forelse($allLinks as $link)
            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="term-panel p-5 group block">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="term-sec-label mb-2">{{ strtoupper($link->category) }}</div>
                        <h3 class="font-semibold text-navy-900 dark:text-white text-sm group-hover:text-accent-soft transition-colors mb-1">{{ $link->title }}</h3>
                        @if($link->description)
                        <p class="text-xs leading-relaxed text-slate-600 dark:text-term-800 line-clamp-2">{{ $link->description }}</p>
                        @endif
                        <div class="mt-2 font-mono text-[10px] tracking-wider text-term-700 truncate">{{ parse_url($link->url, PHP_URL_HOST) }}</div>
                    </div>
                    <svg class="w-4 h-4 text-term-700 group-hover:text-accent-soft flex-shrink-0 mt-0.5 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </div>
            </a>
            @empty
            <div class="term-panel col-span-full p-12 sm:p-20 text-center">
                <div class="font-mono text-[11px] tracking-[0.24em] text-term-700 mb-3">QUERY://EMPTY</div>
                <p class="text-sm text-slate-600 dark:text-term-800">No resources available yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

{{-- Submit link --}}
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" x-data="{ showForm: false }" aria-label="Submit a resource">
    <div class="w-full max-w-2xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-8">
            <span class="term-tag term-tag-accent">RESOURCES://SUBMIT</span>
            <h2 class="mt-4 font-display text-2xl sm:text-3xl font-bold tracking-tight text-navy-900 dark:text-white">Submit a resource</h2>
            <p class="mt-2 text-sm sm:text-base text-slate-600 dark:text-term-800">Know a great cybersecurity tool or IT resource? Share it with us.</p>
        </div>
        <div class="text-center mb-8">
            <button @click="showForm = !showForm" class="term-btn term-btn-ghost term-btn-sm">
                <span x-text="showForm ? 'Close' : 'Suggest a Resource'">Suggest a Resource</span>
            </button>
        </div>
        <div x-show="showForm" x-transition x-cloak style="display:none;">
            <form action="{{ route('useful-links.submit') }}" method="POST" class="term-panel p-6 space-y-5">
                @csrf
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="term-field-label">Your Name</label>
                        <input type="text" name="submitter_name" class="term-input">
                    </div>
                    <div>
                        <label class="term-field-label">Email (optional)</label>
                        <input type="email" name="submitter_email" class="term-input">
                    </div>
                </div>
                <div>
                    <label class="term-field-label">Link Title *</label>
                    <input type="text" name="title" required class="term-input">
                </div>
                <div>
                    <label class="term-field-label">URL *</label>
                    <input type="url" name="url" required class="term-input" placeholder="https://">
                </div>
                <div>
                    <label class="term-field-label">Description</label>
                    <textarea name="description" rows="2" class="term-input resize-none"></textarea>
                </div>
                <div>
                    <label class="term-field-label">Category</label>
                    <select name="category" class="term-input">
                        <option value="cybersecurity">Cybersecurity</option>
                        <option value="tools">Tools</option>
                        <option value="monitoring">Monitoring</option>
                        <option value="cloud">Cloud</option>
                        <option value="general">General</option>
                    </select>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                    <p class="term-hint">Submissions reviewed before publishing.</p>
                    <button type="submit" class="term-btn term-btn-sm">Submit</button>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection
