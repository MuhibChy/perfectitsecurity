@extends('layouts.app')
@section('page-title', 'Asset ' . $asset->asset_tag)

@section('content')
<div class="space-y-6">
    <x-page-header title="Asset {{ $asset->asset_tag }}" sys="OPS://ASSETS/SHOW" />
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="term-panel p-6">
                <h2 class="text-lg font-semibold">{{ $asset->name }}</h2>
                <div class="flex flex-wrap gap-2 my-3"><x-status-badge :status="$asset->status" /><span class="text-sm opacity-70">{{ $asset->category ?? 'Uncategorised' }}</span></div>
                <dl class="grid md:grid-cols-2 gap-2 text-sm">
                    <div><dt class="opacity-60">Manufacturer</dt><dd>{{ $asset->manufacturer ?? '-' }}</dd></div>
                    <div><dt class="opacity-60">Model</dt><dd>{{ $asset->model ?? '-' }}</dd></div>
                    <div><dt class="opacity-60">Serial</dt><dd class="font-mono">{{ $asset->serial_number ?? '-' }}</dd></div>
                    <div><dt class="opacity-60">Location</dt><dd>{{ $asset->location ?? '-' }}</dd></div>
                    <div><dt class="opacity-60">Customer</dt><dd>{{ $asset->customer->name ?? '-' }}</dd></div>
                    <div><dt class="opacity-60">Assigned user</dt><dd>{{ $asset->assignedUser->name ?? '-' }}</dd></div>
                    <div><dt class="opacity-60">Purchased</dt><dd>{{ $asset->purchase_date?->format('Y-m-d') ?? '-' }}</dd></div>
                    <div><dt class="opacity-60">Warranty expires</dt><dd>{{ $asset->warranty_expires?->format('Y-m-d') ?? '-' }}</dd></div>
                </dl>
                @if($asset->notes)<p class="mt-3 whitespace-pre-line">{{ $asset->notes }}</p>@endif
            </div>
            <div class="term-panel p-6">
                <h3 class="font-semibold mb-3">Linked configuration items ({{ $asset->configurationItems->count() }})</h3>
                <ul class="space-y-2">@forelse($asset->configurationItems as $ci)
                    <li><a href="{{ route('admin.ci.show', $ci) }}" class="font-mono text-primary-600 hover:underline">{{ $ci->ci_number }}</a> — {{ $ci->name }}</li>
                    @empty<li class="opacity-70">None.</li>@endforelse</ul>
            </div>
        </div>
        <div class="term-panel p-6 h-fit">
            <h3 class="font-semibold mb-3">Update</h3>
            <form method="POST" action="{{ route('admin.assets.update', $asset) }}" class="space-y-3">@csrf @method('PATCH')
                <select name="status" class="term-input w-full">@foreach(['in_stock','deployed','maintenance','retired'] as $s)<option value="{{ $s }}" {{ $asset->status == $s ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($s)) }}</option>@endforeach</select>
                <input type="text" name="location" value="{{ $asset->location }}" placeholder="Location" class="term-input w-full">
                <textarea name="notes" rows="3" placeholder="Notes" class="term-input w-full">{{ $asset->notes }}</textarea>
                <button class="term-btn term-btn-sm w-full">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection
