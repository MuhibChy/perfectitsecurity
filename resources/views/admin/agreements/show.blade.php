@extends('layouts.app')
@section('page-title', 'Agreement ' . $agreement->agreement_number)

@section('content')
<div class="space-y-6">
    <x-page-header title="Agreement {{ $agreement->agreement_number }}" sys="OPS://AGREEMENTS/SHOW" />
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="term-panel p-6 lg:col-span-2">
            <h2 class="text-lg font-semibold">{{ $agreement->title }}</h2>
            <div class="flex flex-wrap gap-2 my-3"><x-status-badge :status="$agreement->status" /></div>
            <dl class="grid md:grid-cols-2 gap-2 text-sm">
                <div><dt class="opacity-60">Customer</dt><dd>{{ $agreement->customer->name ?? '-' }}</dd></div>
                <div><dt class="opacity-60">Coverage</dt><dd>{{ $agreement->coverage_hours ?? '-' }}</dd></div>
                <div><dt class="opacity-60">Response target</dt><dd>{{ $agreement->response_target_minutes ? $agreement->response_target_minutes . ' min' : '-' }}</dd></div>
                <div><dt class="opacity-60">Resolution target</dt><dd>{{ $agreement->resolution_target_minutes ? $agreement->resolution_target_minutes . ' min' : '-' }}</dd></div>
                <div><dt class="opacity-60">Starts</dt><dd>{{ $agreement->starts_at?->format('Y-m-d') ?? '-' }}</dd></div>
                <div><dt class="opacity-60">Ends</dt><dd>{{ $agreement->ends_at?->format('Y-m-d') ?? '-' }}</dd></div>
            </dl>
            @if($agreement->scope)<h3 class="font-semibold mt-4">Scope</h3><p class="whitespace-pre-line">{{ $agreement->scope }}</p>@endif
        </div>
        <div class="term-panel p-6 h-fit">
            <h3 class="font-semibold mb-3">Manage</h3>
            <form method="POST" action="{{ route('admin.agreements.update', $agreement) }}" class="space-y-3">@csrf @method('PATCH')
                <select name="status" class="term-input w-full">@foreach(['draft','active','suspended','expired','terminated'] as $s)<option value="{{ $s }}" {{ $agreement->status == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>@endforeach</select>
                <input type="date" name="ends_at" value="{{ $agreement->ends_at?->format('Y-m-d') }}" class="term-input w-full">
                <input type="date" name="renewal_reminder_at" value="{{ $agreement->renewal_reminder_at?->format('Y-m-d') }}" class="term-input w-full">
                <button class="term-btn term-btn-sm w-full">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection
