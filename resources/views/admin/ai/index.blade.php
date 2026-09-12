@extends('layouts.app')
@section('page-title', 'AI Support Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Conversations</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_conversations'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Now</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['active_conversations'] }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Escalation Rate</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $escalationRate }}%</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Cost This Month</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${{ number_format($stats['cost_this_month'], 4) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Second Row Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="text-sm text-gray-500">Tickets Created by AI</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['tickets_created'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500">Resolution Rate</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $resolutionRate }}%</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500">Knowledge Gaps</p>
            <p class="text-2xl font-bold {{ $stats['knowledge_gaps'] > 0 ? 'text-red-600' : 'text-gray-900 dark:text-white' }} mt-1">{{ $stats['knowledge_gaps'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500">Total Tokens Used</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_tokens']) }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Recent Conversations --}}
        <div class="lg:col-span-2 glass-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900 dark:text-white">Recent Conversations</h3>
                <a href="{{ route('admin.ai.conversations') }}" class="text-sm text-primary-600 hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>User</th><th>Source</th><th>Status</th><th>Messages</th><th>Created</th></tr></thead>
                    <tbody>
                        @forelse($recentConversations as $conv)
                        <tr>
                            <td class="font-medium">{{ $conv->user->name ?? $conv->guest_name ?? 'Guest' }}</td>
                            <td><span class="badge badge-info">{{ ucfirst($conv->source) }}</span></td>
                            <td>
                                @php $s = ['active'=>'badge-success','escalated'=>'badge-warning','closed'=>'badge-info','archived'=>'badge-purple']; @endphp
                                <span class="badge {{ $s[$conv->status] ?? 'badge-info' }}">{{ ucfirst($conv->status) }}</span>
                            </td>
                            <td>{{ $conv->message_count }}</td>
                            <td class="text-gray-500">{{ $conv->created_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-gray-500 py-4">No conversations yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="space-y-4">
            <a href="{{ route('admin.ai.conversations') }}" class="glass-card p-4 flex items-center gap-4 hover:shadow-lg transition-all group">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center"><svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div>
                <span class="font-medium text-gray-900 dark:text-white">Conversations</span>
            </a>
            <a href="{{ route('admin.ai.knowledge-gaps') }}" class="glass-card p-4 flex items-center gap-4 hover:shadow-lg transition-all group">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center"><svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
                <span class="font-medium text-gray-900 dark:text-white">Knowledge Gaps</span>
                @if($stats['knowledge_gaps'] > 0)<span class="badge badge-danger ml-auto">{{ $stats['knowledge_gaps'] }}</span>@endif
            </a>
            <a href="{{ route('admin.ai.questions') }}" class="glass-card p-4 flex items-center gap-4 hover:shadow-lg transition-all group">
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center"><svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <span class="font-medium text-gray-900 dark:text-white">Common Questions</span>
            </a>
            <a href="{{ route('admin.ai.usage') }}" class="glass-card p-4 flex items-center gap-4 hover:shadow-lg transition-all group">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center"><svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
                <span class="font-medium text-gray-900 dark:text-white">Usage & Costs</span>
            </a>
            <a href="{{ route('admin.ai.settings') }}" class="glass-card p-4 flex items-center gap-4 hover:shadow-lg transition-all group">
                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center"><svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                <span class="font-medium text-gray-900 dark:text-white">AI Settings</span>
            </a>
        </div>
    </div>

    {{-- Most Used Articles --}}
    @if(!empty($mostUsedArticles))
    <div class="glass-card p-6">
        <h3 class="font-bold text-gray-900 dark:text-white mb-4">Most Used KB Articles by AI</h3>
        <div class="space-y-3">
            @foreach($mostUsedArticles as $article)
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-800 last:border-0">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $article['title'] }}</p>
                    <p class="text-xs text-gray-500">{{ $article['category'] }}</p>
                </div>
                <span class="badge badge-info">{{ $article['usage_count'] }} uses</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
