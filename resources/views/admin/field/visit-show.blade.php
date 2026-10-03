@extends('layouts.app')
@section('page-title', 'Visit ' . $visit->visit_number)

@section('content')
<div class="space-y-6">
    <x-page-header title="Visit {{ $visit->visit_number }}" sys="OPS://FIELD/VISITS/SHOW" />
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="term-panel p-6 lg:col-span-2">
            <div class="flex flex-wrap gap-2 mb-4"><x-status-badge :status="$visit->status" /></div>
            <dl class="grid md:grid-cols-2 gap-2 text-sm">
                <div><dt class="opacity-60">Customer</dt><dd>{{ $visit->customer->name ?? '-' }}</dd></div>
                <div><dt class="opacity-60">Technician</dt><dd>{{ $visit->technician->name ?? '-' }}</dd></div>
                <div class="md:col-span-2"><dt class="opacity-60">Address</dt><dd>{{ $visit->address }}</dd></div>
                <div><dt class="opacity-60">Scheduled</dt><dd>{{ $visit->scheduled_at?->format('Y-m-d H:i') ?? '-' }}</dd></div>
                <div><dt class="opacity-60">Check-in / out</dt><dd>{{ $visit->check_in_at?->format('H:i') ?? '-' }} / {{ $visit->check_out_at?->format('H:i') ?? '-' }}</dd></div>
                <div><dt class="opacity-60">Customer confirmation</dt><dd>{{ $visit->customer_confirmed_at ? $visit->customer_signature_name . ' at ' . $visit->customer_confirmed_at->format('Y-m-d H:i') : 'Pending' }}</dd></div>
            </dl>
            @if($visit->work_performed)<h3 class="font-semibold mt-4">Work performed</h3><p class="whitespace-pre-line">{{ $visit->work_performed }}</p>@endif
            @if($visit->parts_used)<h3 class="font-semibold mt-4">Parts used</h3><p class="whitespace-pre-line">{{ $visit->parts_used }}</p>@endif
            @if($visit->follow_up_notes)<h3 class="font-semibold mt-4">Follow-up</h3><p class="whitespace-pre-line">{{ $visit->follow_up_notes }}</p>@endif
        </div>
        <div class="term-panel p-6 h-fit">
            <h3 class="font-semibold mb-3">Manage</h3>
            <form method="POST" action="{{ route('admin.visits.update', $visit) }}" class="space-y-3">@csrf @method('PATCH')
                <select name="technician_id" class="term-input w-full"><option value="">No technician</option>@foreach($technicians as $t)<option value="{{ $t->id }}" {{ $visit->technician_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach</select>
                <select name="status" class="term-input w-full">@foreach(['scheduled','en_route','on_site','completed','cancelled'] as $s)<option value="{{ $s }}" {{ $visit->status == $s ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($s)) }}</option>@endforeach</select>
                <textarea name="work_performed" rows="3" placeholder="Work performed" class="term-input w-full">{{ $visit->work_performed }}</textarea>
                <textarea name="parts_used" rows="2" placeholder="Parts / equipment used" class="term-input w-full">{{ $visit->parts_used }}</textarea>
                <textarea name="follow_up_notes" rows="2" placeholder="Follow-up tasks" class="term-input w-full">{{ $visit->follow_up_notes }}</textarea>
                <button class="term-btn term-btn-sm w-full">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection
