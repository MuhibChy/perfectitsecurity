@extends('layouts.public')

@section('title', 'Resources — TechSupport Solutions')
@section('description', 'Curated cybersecurity tools, threat intelligence, and IT resources.')

@section('content')

{{-- Hero --}}
<section class="section bg-navy-900 dark:bg-navy-950 text-white overflow-hidden">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-20 lg:py-28">
        <div class="max-w-2xl">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-px bg-brand-400"></div>
                <span class="label text-brand-400">Resources</span>
            </div>
            <h1 class="heading-xl text-white mb-6">Cybersecurity tools<br>and resources.</h1>
            <p class="text-lg text-surface-400 leading-relaxed">A curated collection of threat intelligence, security tools, and IT resources for professionals.</p>
        </div>
    </div>
</section>

{{-- Links Grid --}}
<section class="section bg-white dark:bg-navy-900">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-20 lg:py-28">
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($allLinks as $link)
            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="group card-hover p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-navy-900 dark:text-white text-sm group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors mb-1">{{ $link->title }}</h3>
                        @if($link->description)
                        <p class="text-xs text-surface-500 leading-relaxed line-clamp-2">{{ $link->description }}</p>
                        @endif
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-[10px] text-surface-400 uppercase tracking-wide font-medium">{{ ucfirst($link->category) }}</span>
                            <span class="text-surface-300 dark:text-surface-600">·</span>
                            <span class="text-[10px] text-surface-400 truncate">{{ parse_url($link->url, PHP_URL_HOST) }}</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-surface-400 group-hover:text-brand-500 flex-shrink-0 mt-0.5 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </div>
            </a>
            @empty
            <div class="col-span-full py-20 text-center">
                <p class="text-surface-500">No resources available yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

{{-- Submit Link --}}
<section class="section bg-surface-50 dark:bg-navy-800/30" x-data="{ showForm: false }">
    <div class="max-w-2xl mx-auto px-6 lg:px-10 py-20 lg:py-28">
        <div class="text-center mb-8">
            <h2 class="heading-md mb-3">Submit a resource</h2>
            <p class="body-md">Know a great cybersecurity tool or IT resource? Share it with us.</p>
        </div>
        <div class="text-center mb-8">
            <button @click="showForm = !showForm" class="btn-secondary px-6 py-2.5 text-sm">
                <span x-text="showForm ? 'Close' : 'Suggest a Resource'">Suggest a Resource</span>
            </button>
        </div>
        <div x-show="showForm" x-transition x-cloak>
            <form action="{{ route('useful-links.submit') }}" method="POST" class="card p-6 space-y-5">
                @csrf
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1.5">Your Name</label>
                        <input type="text" name="submitter_name" class="w-full px-3.5 py-2.5 rounded-lg border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800/50 text-navy-900 dark:text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1.5">Email (optional)</label>
                        <input type="email" name="submitter_email" class="w-full px-3.5 py-2.5 rounded-lg border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800/50 text-navy-900 dark:text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1.5">Link Title *</label>
                    <input type="text" name="title" required class="w-full px-3.5 py-2.5 rounded-lg border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800/50 text-navy-900 dark:text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1.5">URL *</label>
                    <input type="url" name="url" required class="w-full px-3.5 py-2.5 rounded-lg border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800/50 text-navy-900 dark:text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="https://">
                </div>
                <div>
                    <label class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1.5">Description</label>
                    <textarea name="description" rows="2" class="w-full px-3.5 py-2.5 rounded-lg border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800/50 text-navy-900 dark:text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1.5">Category</label>
                    <select name="category" class="w-full px-3.5 py-2.5 rounded-lg border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800/50 text-navy-900 dark:text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        <option value="cybersecurity">Cybersecurity</option>
                        <option value="tools">Tools</option>
                        <option value="monitoring">Monitoring</option>
                        <option value="cloud">Cloud</option>
                        <option value="general">General</option>
                    </select>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <p class="text-xs text-surface-400">Submissions reviewed before publishing.</p>
                    <button type="submit" class="btn-primary px-6 py-2.5 text-sm">Submit</button>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection
