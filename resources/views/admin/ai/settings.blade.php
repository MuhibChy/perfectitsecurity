@extends('layouts.app')
@section('page-title', 'AI Settings')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="glass-card p-6">
        <form method="POST" action="{{ route('admin.ai.settings.update') }}" class="space-y-6">
            @csrf
            <h3 class="font-bold text-gray-900 dark:text-white">AI Provider Configuration</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">AI Provider</label>
                    <select name="ai_provider" class="input-field">
                        <option value="openai" {{ ($settings['ai_provider'] ?? 'openai') === 'openai' ? 'selected' : '' }}>OpenAI</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Set API key in .env file (AI_API_KEY)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Model</label>
                    <select name="ai_model" class="input-field">
                        <option value="gpt-4o-mini" {{ ($settings['ai_model'] ?? 'gpt-4o-mini') === 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini (Recommended)</option>
                        <option value="gpt-4o" {{ ($settings['ai_model'] ?? '') === 'gpt-4o' ? 'selected' : '' }}>GPT-4o</option>
                        <option value="gpt-3.5-turbo" {{ ($settings['ai_model'] ?? '') === 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
                    </select>
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">System Prompt</label>
                <textarea name="ai_system_prompt" rows="6" class="input-field" placeholder="Instructions for the AI assistant...">{{ $settings['ai_system_prompt'] ?? '' }}</textarea>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <h3 class="font-bold text-gray-900 dark:text-white">Rate Limits</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Daily Message Limit</label>
                    <input type="number" name="daily_message_limit" value="{{ $settings['daily_message_limit'] ?? 1000 }}" class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Monthly Message Limit</label>
                    <input type="number" name="monthly_message_limit" value="{{ $settings['monthly_message_limit'] ?? 30000 }}" class="input-field">
                </div>
            </div>

            <h3 class="font-bold text-gray-900 dark:text-white">Cost Limits</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Daily Cost Limit ($)</label>
                    <input type="number" step="0.01" name="daily_cost_limit" value="{{ $settings['daily_cost_limit'] ?? 50 }}" class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Monthly Cost Limit ($)</label>
                    <input type="number" step="0.01" name="monthly_cost_limit" value="{{ $settings['monthly_cost_limit'] ?? 500 }}" class="input-field">
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
                <button type="submit" class="btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
