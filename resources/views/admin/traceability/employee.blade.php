@extends('layouts.app')
@section('title', 'Employee Work History')
@section('page-title', 'Work History')

@section('content')
<x-page-header sys="OPS://TRACEABILITY" :title="$user->name . ' — Work History'" :subtitle="$user->roleDisplayName() . ' · ' . $user->email">
    <x-slot:actions>
        <x-role-badge :role="$user->role" />
        @if(auth()->id() === $user->id)
        <a href="{{ route('admin.reports.my-work', ['format' => 'pdf']) }}" class="term-btn term-btn-sm">Download My Report (PDF)</a>
        <a href="{{ route('admin.reports.my-work', ['format' => 'csv']) }}" class="term-btn term-btn-ghost term-btn-sm">CSV</a>
        <a href="{{ route('admin.reports.my-work', ['format' => 'xlsx']) }}" class="term-btn term-btn-ghost term-btn-sm">XLSX</a>
        @endif
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([['Assigned tasks',$overview['tasks_assigned']],['Completed',$overview['tasks_completed']],['In progress',$overview['tasks_in_progress']],['Completed this month',$overview['completed_this_month']],['Projects managed',$overview['projects_managed']],['Tickets resolved',$overview['tickets_resolved'].'/'.$overview['tickets_assigned']],['Work updates',$overview['task_comments'] + $overview['ticket_messages']],['Recorded actions',$overview['recorded_actions']]] as [$label,$val])
    <div class="term-panel"><div class="stat-value">{{ $val }}</div><div class="stat-label">{{ $label }}</div></div>
    @endforeach
</div>

@if($links->isNotEmpty())
<h2 class="heading-sm mb-3">Employee → Customer → Service Links</h2>
<div class="card p-0 overflow-hidden mb-6"><table class="data-table term-table">
    <thead><tr><th>Customer</th><th>Through</th><th>Project</th><th>Date</th></tr></thead>
    <tbody>@foreach($links as $l)<tr><td class="font-medium" data-label="Customer">{{ $l['customer']->name }}</td><td data-label="Through">{{ $l['via'] }}</td><td data-label="Project">{{ $l['project']->name ?? '—' }}</td><td data-label="Date">{{ \Carbon\Carbon::parse($l['at'])->format('d M Y') }}</td></tr>@endforeach</tbody>
</table></div>
@endif

<h2 class="heading-sm mb-3">Activity Timeline (from actual records)</h2>
<x-timeline :timeline="$timeline" />
@endsection
