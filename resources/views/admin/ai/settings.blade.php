@extends('layouts.app')
@section('page-title', 'AI Settings')
@section('content')

    <x-page-header title="AI Settings" sys="SYSTEM://AI" />
<div class="max-w-3xl mx-auto">
    <div class="term-panel p-6">
        <form method="POST" action="{{ route('admin.ai.settings.update') }}" class="space-y-6">
            @csrf
            <h3 class="font-bold text-gray-900 dark:text-white">AI Provider Configuration</h3>
            @isset($health)
            <div class="border border-white/10 bg-black/40 p-4 text-xs">
                <div class="font-bold text-gray-900 dark:text-white mb-2">Provider Health (admin only)</div>
                <div class="grid sm:grid-cols-2 gap-x-4 gap-y-1 text-gray-600 dark:text-gray-300">
                    <div>Provider: <span class="font-mono">{{ $health['provider'] ?? 'unknown' }}</span></div>
                    <div>Connection: <span class="font-bold {{ !empty($health['reachable']) ? 'text-white' : '' }}">{{ !empty($health['reachable']) ? 'Online' : 'Offline' }}</span></div>
                    <div>Model: <span class="font-mono">{{ $health['model'] ?? '—' }}</span></div>
                    <div>Model available: <span class="font-bold">{{ !empty($health['model_available']) ? 'Yes' : 'No' }}</span></div>
                    <div>Latency: {{ $health['latency_ms'] !== null ? $health['latency_ms'] . ' ms' : '—' }}</div>
                    <div>Checked: {{ $health['checked_at'] ?? '—' }}</div>
                    @if(!empty($health['error']))
                    <div class="sm:col-span-2">Issue: <span class="font-mono">{{ $health['error'] }}</span></div>
                    @endif
                </div>
            </div>
            @endisset
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="term-field-label">AI Provider</label>
                    <select name="ai_provider" class="term-input">
                        <option value="ollama" {{ ($settings['ai_provider'] ?? 'ollama') === 'ollama' ? 'selected' : '' }}>Ollama (local server)</option>
                        <option value="openrouter" {{ ($settings['ai_provider'] ?? '') === 'openrouter' ? 'selected' : '' }}>OpenRouter (cloud models)</option>
                        <option value="openai" {{ ($settings['ai_provider'] ?? '') === 'openai' ? 'selected' : '' }}>OpenAI</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Ollama uses OLLAMA_* in .env. OpenRouter/OpenAI need their API key in .env.</p>
                </div>
                <div>
                    <label class="term-field-label">Model</label>
                    <input type="text" name="ai_model" value="{{ $settings['ai_model'] ?? '' }}" class="term-input font-mono" list="ai-models" placeholder="e.g. llama3.2:latest">
                    <datalist id="ai-models">
                        <option value="llama3.2:latest"></option>
                        <option value="qwen2.5-coder:14b"></option>
                        <option value="elli:latest"></option>
                        <option value="gemma4:26b"></option>
                        <option value="meta-llama/llama-3.2-3b-instruct"></option>
                        <option value="meta-llama/llama-3.1-8b-instruct"></option>
                        <option value="gpt-4o-mini"></option>
                        <option value="gpt-4o"></option>
                    </datalist>
                    <p class="text-xs text-gray-500 mt-1">Must be installed on the provider (Ollama: check GET /api/tags).</p>
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="ai_enabled" value="1" {{ ($settings['ai_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable AI Assistant</span>
                </label>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <h3 class="font-bold text-gray-900 dark:text-white">System Instructions</h3>
            <div>
                <label class="term-field-label">System Prompt</label>
                <textarea name="ai_system_prompt" rows="6" class="term-input" placeholder="Instructions for the AI assistant...">{{ $settings['ai_system_prompt'] ?? '' }}</textarea>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <h3 class="font-bold text-gray-900 dark:text-white">Rate Limits</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="term-field-label">Daily Message Limit</label>
                    <input type="number" name="daily_message_limit" value="{{ $settings['daily_message_limit'] ?? 1000 }}" class="term-input">
                </div>
                <div>
                    <label class="term-field-label">Monthly Message Limit</label>
                    <input type="number" name="monthly_message_limit" value="{{ $settings['monthly_message_limit'] ?? 30000 }}" class="term-input">
                </div>
            </div>

            <h3 class="font-bold text-gray-900 dark:text-white">Cost Limits</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="term-field-label">Daily Cost Limit ($)</label>
                    <input type="number" step="0.01" name="daily_cost_limit" value="{{ $settings['daily_cost_limit'] ?? 50 }}" class="term-input">
                </div>
                <div>
                    <label class="term-field-label">Monthly Cost Limit ($)</label>
                    <input type="number" step="0.01" name="monthly_cost_limit" value="{{ $settings['monthly_cost_limit'] ?? 500 }}" class="term-input">
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <h3 class="font-bold text-gray-900 dark:text-white">Features</h3>
            <div class="space-y-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="escalation_enabled" value="1" {{ ($settings['escalation_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Allow escalation to human support</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="ticket_creation_enabled" value="1" {{ ($settings['ticket_creation_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Allow AI to create support tickets</span>
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="term-btn">Save Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
