@extends('layouts.app')
@section('page-title', 'My Agreements')
@section('content')
<div class="space-y-6">
    <x-page-header title="My Service Agreements" subtitle="Coverage, targets and renewal dates." sys="SUPPORT://AGREEMENTS" />
    <div class="grid md:grid-cols-2 gap-6">
        @forelse($agreements as $a)
        <div class="term-panel p-6">
            <div class="flex items-center justify-between mb-2"><span class="font-mono text-sm">{{ $a->agreement_number }}</span><x-status-badge :status="$a->status" /></div>
            <h2 class="font-semibold text-lg">{{ $a->title }}</h2>
            @if($a->scope)<p class="text-sm mt-2 whitespace-pre-line">{{ $a->scope }}</p>@endif
            <dl class="grid grid-cols-2 gap-2 text-sm mt-3">
                <div><dt class="opacity-60">Coverage</dt><dd>{{ $a->coverage_hours ?? '-' }}</dd></div>
                <div><dt class="opacity-60">Response target</dt><dd>{{ $a->response_target_minutes ? $a->response_target_minutes . ' min' : '-' }}</dd></div>
                <div><dt class="opacity-60">Resolution target</dt><dd>{{ $a->resolution_target_minutes ? $a->resolution_target_minutes . ' min' : '-' }}</dd></div>
                <div><dt class="opacity-60">Valid until</dt><dd>{{ $a->ends_at?->format('Y-m-d') ?? '-' }}</dd></div>
            </dl>
        </div>
        @empty<div class="term-panel p-6">No service agreements on your account yet.</div>@endforelse
    </div>
    <div>{{ $agreements->links() }}</div>
</div>
@endsection
