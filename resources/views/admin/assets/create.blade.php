@extends('layouts.app')
@section('page-title', 'New Asset')

@section('content')
<div class="space-y-6">
    <x-page-header title="New Asset" sys="OPS://ASSETS/NEW" />
    <div class="term-panel p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.assets.store') }}" class="space-y-4">@csrf
            <div><label class="block text-sm mb-1">Name</label><input type="text" name="name" value="{{ old('name') }}" required class="term-input w-full"></div>
            <div class="grid md:grid-cols-2 gap-4">
                <div><label class="block text-sm mb-1">Category</label><input type="text" name="category" value="{{ old('category') }}" placeholder="Laptop / Server / Router..." class="term-input w-full"></div>
                <div><label class="block text-sm mb-1">Status</label><select name="status" class="term-input w-full">@foreach(['in_stock','deployed','maintenance','retired'] as $s)<option value="{{ $s }}">{{ str_replace('_', ' ', ucfirst($s)) }}</option>@endforeach</select></div>
            </div>
            <div class="grid md:grid-cols-3 gap-4">
                <div><label class="block text-sm mb-1">Manufacturer</label><input type="text" name="manufacturer" class="term-input w-full"></div>
                <div><label class="block text-sm mb-1">Model</label><input type="text" name="model" class="term-input w-full"></div>
                <div><label class="block text-sm mb-1">Serial number</label><input type="text" name="serial_number" class="term-input w-full"></div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div><label class="block text-sm mb-1">Customer (optional)</label><select name="customer_id" class="term-input w-full"><option value="">Unassigned</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
                <div><label class="block text-sm mb-1">Location</label><input type="text" name="location" class="term-input w-full"></div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div><label class="block text-sm mb-1">Purchase date</label><input type="date" name="purchase_date" class="term-input w-full"></div>
                <div><label class="block text-sm mb-1">Warranty expires</label><input type="date" name="warranty_expires" class="term-input w-full"></div>
            </div>
            <div><label class="block text-sm mb-1">Notes</label><textarea name="notes" rows="3" class="term-input w-full"></textarea></div>
            <button type="submit" class="term-btn">Record Asset</button>
        </form>
    </div>
</div>
@endsection
