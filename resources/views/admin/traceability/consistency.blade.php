@extends('layouts.app')
@section('title', 'Data Consistency Check')
@section('page-title', 'Consistency')

@section('content')
<x-page-header sys="OPS://TRACEABILITY" title="Data Consistency Check" subtitle="Read-only integrity scan across customers, orders, invoices, payments, projects, tasks and tickets." badge="ADMIN ONLY" :breadcrumbs="['Audit' => route('admin.history.audit'), 'Consistency' => null]" />

@if(empty($issues))
<div class="term-panel p-8 text-center"><p class="heading-sm">All checks clean ✓</p><p class="body-md">No orphaned, mismatched or misassigned records detected.</p></div>
@else
@foreach($issues as $issue)
<div class="card p-5 mb-4 border-l-4 border-l-amber-500">
    <h2 class="heading-sm mb-1">{{ $issue['label'] }} — {{ $issue['count'] }}</h2>
    <ul class="list-disc list-inside text-sm text-slate-600 dark:text-slate-300">@foreach($issue['samples'] as $s)<li class="font-mono">{{ $s }}</li>@endforeach</ul>
</div>
@endforeach
@endif
@endsection
