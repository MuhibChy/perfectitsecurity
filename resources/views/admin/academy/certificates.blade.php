@extends('layouts.app')
@section('title', 'My Completion Records')
@section('page-title', 'My Completion Records')

@section('content')
<x-page-header sys="CONTENT://ACADEMY" title="Completion Records" subtitle="Internal training completion — distinct from external professional certification." :breadcrumbs="['Academy' => route('admin.academy.index'), 'Records' => null]" />

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @forelse($certificates as $cert)
    <div class="card p-5 flex items-center justify-between gap-3">
        <div><div class="font-semibold">{{ $cert->course->title }}</div><div class="text-xs text-slate-500">{{ $cert->certificate_no }} · Score {{ $cert->score ?? '—' }}% · v{{ $cert->course_version }} · {{ $cert->completed_at?->format('M d, Y') }}</div></div>
        <a href="{{ route('admin.academy.certificate', $cert) }}" class="term-btn term-btn-ghost term-btn-sm">View record</a>
    </div>
    @empty
    <p class="body-md">No completion records yet.</p>
    @endforelse
</div>
@endsection
