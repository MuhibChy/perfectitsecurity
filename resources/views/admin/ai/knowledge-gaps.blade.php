@extends('layouts.app')
@section('page-title', 'Knowledge Gaps')
@section('content')
<div class="space-y-6">
    <x-page-header title="Knowledge Gaps" sys="SYSTEM://AI" />
    <div class="term-panel overflow-hidden">
        <div class="overflow-x-auto term-table-wrap">
            <table class="data-table term-table">
                <thead><tr><th>Question</th><th>Occurrences</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($gaps as $gap)
                    <tr>
                        <td class="font-medium max-w-md truncate" data-label="Question">{{ $gap->question }}</td>
                        <td data-label="Occurrences"><span class="term-tag">{{ $gap->occurrence_count }}</span></td>
                        <td data-label="Status">
                            @php $s = ['detected'=>'','reviewing'=>'','resolved'=>'','dismissed'=>'']; @endphp
                            <span class="term-tag {{ $s[$gap->status] }}">{{ ucfirst($gap->status) }}</span>
                        </td>
                        <td class="text-gray-500" data-label="Created">{{ $gap->created_at->diffForHumans() }}</td>
                        <td data-label="Actions">
                            <form method="POST" action="{{ route('admin.ai.gap.update', $gap) }}" class="inline-flex gap-1">
                                @csrf
                                <select name="status" class="term-input max-w-[120px]">
                                    <option value="detected" {{ $gap->status === 'detected' ? 'selected' : '' }}>Detected</option>
                                    <option value="reviewing" {{ $gap->status === 'reviewing' ? 'selected' : '' }}>Reviewing</option>
                                    <option value="resolved" {{ $gap->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="dismissed" {{ $gap->status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                                </select>
                                <button type="submit" class="term-btn term-btn-ghost term-btn-sm">Save</button>
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
