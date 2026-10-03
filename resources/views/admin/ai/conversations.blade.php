@extends('layouts.app')
@section('page-title', 'AI Conversations')
@section('content')
<div class="space-y-6">
    <x-page-header title="AI Conversations" sys="SYSTEM://AI" />
    <div class="term-panel p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search conversations..." class="term-input max-w-xs">
            <select name="status" class="term-input max-w-xs">
                <option value="">All Status</option>
                @foreach(['active','escalated','closed','archived'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="term-btn term-btn-sm">Filter</button>
        </form>
    </div>

    <div class="term-panel overflow-hidden">
        <div class="overflow-x-auto term-table-wrap">
            <table class="data-table term-table">
                <thead><tr><th>User</th><th>Email</th><th>Source</th><th>Status</th><th>Messages</th><th>Escalated</th><th>Created</th><th></th></tr></thead>
                <tbody>
                    @forelse($conversations as $conv)
                    <tr>
                        <td class="font-medium" data-label="User">{{ $conv->user->name ?? $conv->guest_name ?? 'Guest' }}</td>
                        <td class="text-sm text-gray-500" data-label="Email">{{ $conv->user->email ?? $conv->guest_email ?? '-' }}</td>
                        <td data-label="Source"><span class="term-tag">{{ ucfirst($conv->source) }}</span></td>
                        <td data-label="Status">
                            @php $s = ['active'=>'','escalated'=>'','closed'=>'','archived'=>'']; @endphp
                            <span class="term-tag {{ $s[$conv->status] ?? '' }}">{{ ucfirst($conv->status) }}</span>
                        </td>
                        <td data-label="Messages">{{ $conv->message_count }}</td>
                        <td data-label="Escalated">{{ $conv->escalated_at ? $conv->escalated_at->diffForHumans() : '-' }}</td>
                        <td class="text-gray-500" data-label="Created">{{ $conv->created_at->diffForHumans() }}</td>
                        <td data-label=""><a href="{{ route('admin.ai.conversation.show', $conv) }}" class="term-btn term-btn-ghost term-btn-sm">View</a></td>
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
