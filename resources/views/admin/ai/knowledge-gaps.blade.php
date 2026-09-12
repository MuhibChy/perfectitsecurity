@extends('layouts.app')
@section('page-title', 'Knowledge Gaps')
@section('content')
<div class="space-y-6">
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Question</th><th>Occurrences</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($gaps as $gap)
                    <tr>
                        <td class="font-medium max-w-md truncate">{{ $gap->question }}</td>
                        <td><span class="badge badge-warning">{{ $gap->occurrence_count }}</span></td>
                        <td>
                            @php $s = ['detected'=>'badge-danger','reviewing'=>'badge-warning','resolved'=>'badge-success','dismissed'=>'badge-info']; @endphp
                            <span class="badge {{ $s[$gap->status] }}">{{ ucfirst($gap->status) }}</span>
                        </td>
                        <td class="text-gray-500">{{ $gap->created_at->diffForHumans() }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.ai.gap.update', $gap) }}" class="inline-flex gap-1">
                                @csrf
                                <select name="status" class="input-field text-xs py-1 px-2 max-w-[120px]">
                                    <option value="detected" {{ $gap->status === 'detected' ? 'selected' : '' }}>Detected</option>
                                    <option value="reviewing" {{ $gap->status === 'reviewing' ? 'selected' : '' }}>Reviewing</option>
                                    <option value="resolved" {{ $gap->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="dismissed" {{ $gap->status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                                </select>
                                <button type="submit" class="btn-ghost btn-sm text-xs">Save</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-gray-500 py-8">No knowledge gaps detected. The AI is handling questions well!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">{{ $gaps->links() }}</div>
    </div>
</div>
@endsection
