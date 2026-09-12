@extends('layouts.app')
@section('page-title', 'Common Questions')
@section('content')
<div class="glass-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Question</th><th>Count</th></tr></thead>
            <tbody>
                @forelse($questions as $q)
                <tr>
                    <td class="font-medium">{{ $q['content'] }}</td>
                    <td><span class="badge badge-info">{{ $q['count'] }}</span></td>
                </tr>
                @empty
                <tr><td colspan="2" class="text-center text-gray-500 py-8">No questions recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
