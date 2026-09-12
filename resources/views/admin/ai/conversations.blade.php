@extends('layouts.app')
@section('page-title', 'AI Conversations')
@section('content')
<div class="space-y-6">
    <div class="glass-card p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search conversations..." class="input-field max-w-xs">
            <select name="status" class="input-field max-w-xs">
                <option value="">All Status</option>
                @foreach(['active','escalated','closed','archived'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary btn-sm">Filter</button>
        </form>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>User</th><th>Email</th><th>Source</th><th>Status</th><th>Messages</th><th>Escalated</th><th>Created</th><th></th></tr></thead>
                <tbody>
                    @forelse($conversations as $conv)
                    <tr>
                        <td class="font-medium">{{ $conv->user->name ?? $conv->guest_name ?? 'Guest' }}</td>
                        <td class="text-sm text-gray-500">{{ $conv->user->email ?? $conv->guest_email ?? '-' }}</td>
                        <td><span class="badge badge-info">{{ ucfirst($conv->source) }}</span></td>
                        <td>
                            @php $s = ['active'=>'badge-success','escalated'=>'badge-warning','closed'=>'badge-info','archived'=>'badge-purple']; @endphp
                            <span class="badge {{ $s[$conv->status] ?? 'badge-info' }}">{{ ucfirst($conv->status) }}</span>
                        </td>
                        <td>{{ $conv->message_count }}</td>
                        <td>{{ $conv->escalated_at ? $conv->escalated_at->diffForHumans() : '-' }}</td>
                        <td class="text-gray-500">{{ $conv->created_at->diffForHumans() }}</td>
                        <td><a href="{{ route('admin.ai.conversation.show', $conv) }}" class="btn-ghost btn-sm">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-gray-500 py-8">No conversations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">{{ $conversations->links() }}</div>
    </div>
</div>
@endsection
