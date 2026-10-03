@extends('layouts.app')
@section('page-title', 'AI Testing Bench')
@section('content')

    <x-page-header title="AI Testing Bench" sys="SYSTEM://AI" />
<div class="space-y-6 max-w-3xl mx-auto">
    <div class="term-panel p-6">
        <form method="POST" action="{{ route('admin.ai.test-bench.run') }}" class="space-y-4">
            @csrf
            <div>
                <label class="term-field-label">Test question (as a customer would ask it)</label>
                <input type="text" name="question" value="{{ old('question', $result['question'] ?? '') }}" required maxlength="2000" class="term-input" placeholder="Which service should I choose for securing my business website?">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="term-btn term-btn-sm">Run Diagnosis</button>
            </div>
        </form>
    </div>

    @if($result)
    <div class="term-panel p-6 space-y-4">
        <h3 class="font-bold text-gray-900 dark:text-white">Routing Diagnosis <span class="text-xs font-normal text-gray-500">(no model call made)</span></h3>
        <div class="grid sm:grid-cols-2 gap-3 text-sm">
            <div><span class="text-gray-500">Detected Skill:</span> <span class="font-mono font-bold">{{ $result['skill']['name'] ?? 'None' }}</span>@if($result['skill']) <span class="text-xs text-gray-500">({{ $result['skill']['slug'] }})</span>@endif</div>
            <div><span class="text-gray-500">Top KB Score:</span> <span class="font-mono font-bold">{{ $result['top_score'] }}</span> <span class="text-xs text-gray-500">(threshold {{ $result['threshold'] }})</span></div>
            <div><span class="text-gray-500">Source:</span> <span class="term-tag {{ $result['source'] === 'internal' ? '' : '' }}">{{ $result['source'] === 'internal' ? 'Knowledge Base + Service Catalogue' : 'Local Ollama (general)' }}</span></div>
            <div><span class="text-gray-500">Ollama Used:</span> <span class="font-bold">{{ $result['ollama_used'] ? 'YES' : 'NO' }}</span>@if($result['ollama_used']) <span class="text-xs text-gray-500 font-mono">({{ $result['model'] }})</span>@endif</div>
        </div>
        <div>
            <div class="text-sm text-gray-500 mb-2">Knowledge Results:</div>
            @forelse($result['articles'] as $a)
            <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-100 dark:border-white/5">
                <span>{{ $a['title'] }} <span class="text-xs text-gray-400">({{ $a['visibility'] }})</span></span>
                <span class="font-mono text-xs">score {{ $a['score'] }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-500">No reliable result — would fall back to Ollama general guidance.</p>
            @endforelse
        </div>
    </div>
    @endif
</div>
@endsection
