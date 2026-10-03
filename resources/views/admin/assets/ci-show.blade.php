@extends('layouts.app')
@section('page-title', 'CI ' . $ci->ci_number)

@section('content')
<div class="space-y-6">
    <x-page-header title="CI {{ $ci->ci_number }}" sys="OPS://CMDB/SHOW" />
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="term-panel p-6 lg:col-span-2">
            <h2 class="text-lg font-semibold">{{ $ci->name }}</h2>
            <div class="flex flex-wrap gap-2 my-3"><x-status-badge :status="$ci->ci_type" /><x-status-badge :status="$ci->status" /><x-status-badge :status="$ci->criticality" /></div>
            <dl class="grid md:grid-cols-2 gap-2 text-sm">
                <div><dt class="opacity-60">Identifier</dt><dd class="font-mono">{{ $ci->identifier ?? '-' }}</dd></div>
                <div><dt class="opacity-60">Environment</dt><dd>{{ ucfirst($ci->environment) }}</dd></div>
                <div><dt class="opacity-60">Customer</dt><dd>{{ $ci->customer->name ?? '-' }}</dd></div>
                <div><dt class="opacity-60">Asset</dt><dd>{{ $ci->asset->asset_tag ?? '-' }}</dd></div>
                <div><dt class="opacity-60">Owner</dt><dd>{{ $ci->owner->name ?? '-' }}</dd></div>
            </dl>
        </div>
        <div class="term-panel p-6 h-fit">
            <h3 class="font-semibold mb-3">Depends on / connected to</h3>
            <ul class="space-y-2 mb-4">@forelse($ci->childRelationships as $rel)
                <li class="flex items-center justify-between gap-2 text-sm"><span><a href="{{ route('admin.ci.show', $rel->child) }}" class="font-mono text-primary-600 hover:underline">{{ $rel->child->ci_number }}</a> <span class="opacity-60">({{ str_replace('_', ' ', $rel->relationship_type) }})</span></span>
                <form method="POST" action="{{ route('admin.ci.unrelate', [$ci, $rel]) }}">@csrf @method('DELETE')<button class="term-btn term-btn-sm">Remove</button></form></li>
                @empty<li class="opacity-70 text-sm">No dependencies recorded.</li>@endforelse</ul>
            @if($ci->parentRelationships->count())
            <h3 class="font-semibold mb-2">Used by</h3>
            <ul class="space-y-1 mb-4 text-sm">@foreach($ci->parentRelationships as $rel)<li><a href="{{ route('admin.ci.show', $rel->parent) }}" class="font-mono text-primary-600 hover:underline">{{ $rel->parent->ci_number }}</a></li>@endforeach</ul>
            @endif
            <form method="POST" action="{{ route('admin.ci.relate', $ci) }}" class="space-y-2">@csrf
                <select name="child_ci_id" class="term-input w-full" required><option value="">Select CI...</option>@foreach($candidates as $c)<option value="{{ $c->id }}">{{ $c->ci_number }} — {{ $c->name }}</option>@endforeach</select>
                <select name="relationship_type" class="term-input w-full"><option value="depends_on">Depends on</option><option value="hosts">Hosts</option><option value="runs_on">Runs on</option><option value="connects_to">Connects to</option></select>
                <button class="term-btn term-btn-sm w-full">Link</button>
            </form>
        </div>
    </div>
</div>
@endsection
