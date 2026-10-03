@extends('layouts.app')
@section('page-title', 'Common Questions')
@section('content')

    <x-page-header title="Common Questions" sys="SYSTEM://AI" />
<div class="term-panel overflow-hidden">
    <div class="overflow-x-auto term-table-wrap">
        <table class="data-table term-table">
            <thead><tr><th>Question</th><th>Count</th></tr></thead>
            <tbody>
                @forelse($questions as $q)
                <tr>
                    <td class="font-medium" data-label="Question">{{ $q['content'] }}</td>
                    <td data-label="Count"><span class="term-tag">{{ $q['count'] }}</span></td>
                </tr>
                @empty
                <tr><td colspan="2" class="text-center text-gray-500 py-8">No questions recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
