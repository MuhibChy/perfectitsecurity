@extends('layouts.app')
@section('title', ($provider->exists ? 'Edit' : 'Add') . ' Payment Provider')
@section('page-title', 'Payment Providers')

@section('content')
<x-page-header sys="PAYMENTS://PROVIDERS" title="{{ $provider->exists ? 'Edit provider' : 'Add provider' }}" subtitle="Blank secret fields keep the stored secrets unchanged." :breadcrumbs="['Providers' => route('admin.payment-providers.index'), ($provider->exists ? 'Edit' : 'Add') => null]" />

<div class="term-panel p-4 max-w-3xl">
<form method="POST" action="{{ $provider->exists ? route('admin.payment-providers.update', $provider) : route('admin.payment-providers.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
    @csrf
    @if($provider->exists) @method('PUT') @endif
    <label class="block">Key (stable, lowercase)<input name="key" value="{{ old('key', $provider->key) }}" class="term-input w-full font-mono" required @if($provider->exists) readonly @endif></label>
    <label class="block">Display name<input name="name" value="{{ old('name', $provider->name) }}" class="term-input w-full" required></label>
    <label class="block">Type<select name="type" class="term-input w-full">@foreach(\App\Models\PaymentProvider::TYPES as $t)<option value="{{ $t }}" @selected(old('type', $provider->type) === $t)>{{ ucfirst($t) }}</option>@endforeach</select></label>
    <label class="block">Country<input name="country" value="{{ old('country', $provider->country) }}" class="term-input w-full" placeholder="Bangladesh / International"></label>
    <label class="block">Currencies (comma separated, blank = all)<input name="currencies" value="{{ old('currencies', $provider->currencies ? implode(',', $provider->currencies) : '') }}" class="term-input w-full font-mono" placeholder="BDT"></label>
    <label class="block">Payment methods (comma separated)<input name="payment_methods" value="{{ old('payment_methods', $provider->payment_methods ? implode(',', $provider->payment_methods) : '') }}" class="term-input w-full font-mono" placeholder="checkout,refund"></label>
    <label class="block">Environment<select name="environment" class="term-input w-full">@foreach(\App\Models\PaymentProvider::ENVIRONMENTS as $e)<option value="{{ $e }}" @selected(old('environment', $provider->environment) === $e)>{{ strtoupper($e) }}</option>@endforeach</select></label>
    <label class="block">Status<select name="status" class="term-input w-full">@foreach(\App\Models\PaymentProvider::STATUSES as $s)<option value="{{ $s }}" @selected(old('status', $provider->status) === $s)>{{ ucfirst($s) }}</option>@endforeach</select></label>
    <label class="block">Priority (lower = preferred)<input type="number" name="priority" value="{{ old('priority', $provider->priority) }}" class="term-input w-full" min="1" max="1000"></label>
    <label class="block">Active<select name="is_active" class="term-input w-full"><option value="1" @selected(old('is_active', $provider->is_active))>Yes</option><option value="0" @selected(!old('is_active', $provider->is_active))>No</option></select></label>
    <label class="block">Min amount<input type="number" step="0.01" name="min_amount" value="{{ old('min_amount', $provider->min_amount) }}" class="term-input w-full"></label>
    <label class="block">Max amount<input type="number" step="0.01" name="max_amount" value="{{ old('max_amount', $provider->max_amount) }}" class="term-input w-full"></label>
    <label class="block">Fee type<select name="fee_type" class="term-input w-full"><option value="">none</option>@foreach(['fixed','percent','mixed'] as $f)<option value="{{ $f }}" @selected(old('fee_type', $provider->fee_type) === $f)>{{ ucfirst($f) }}</option>@endforeach</select></label>
    <label class="block">Fee value<input type="number" step="0.0001" name="fee_value" value="{{ old('fee_value', $provider->fee_value) }}" class="term-input w-full"></label>
    <label class="block md:col-span-2">Webhook URL (blank = default per-provider endpoint)<input name="webhook_url" value="{{ old('webhook_url', $provider->webhook_url) }}" class="term-input w-full font-mono"></label>
    <div class="md:col-span-2 border-t border-slate-700 pt-3 mt-1">
        <div class="font-semibold mb-1">Secrets (encrypted at rest — leave blank to keep)</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <label class="block">API key<input name="cred_api_key" type="password" autocomplete="off" class="term-input w-full font-mono" placeholder="••••••"></label>
            <label class="block">Secret key<input name="cred_secret_key" type="password" autocomplete="off" class="term-input w-full font-mono" placeholder="••••••"></label>
            <label class="block">Merchant ID<input name="cred_merchant_id" class="term-input w-full font-mono" placeholder="••••••"></label>
            <label class="block">Account ID<input name="cred_account_id" class="term-input w-full font-mono" placeholder="••••••"></label>
            <label class="block">Public key<input name="cred_public_key" class="term-input w-full font-mono" placeholder="••••••"></label>
            <label class="block">Webhook secret<input name="webhook_secret" type="password" autocomplete="off" class="term-input w-full font-mono" placeholder="••••••"></label>
        </div>
        <label class="block mt-3">Extra credentials (JSON object)<input name="cred_extra" class="term-input w-full font-mono" placeholder='{"username":"…"}'></label>
    </div>
    <label class="block md:col-span-2">Internal notes<textarea name="notes" rows="2" class="term-input w-full">{{ old('notes', $provider->notes) }}</textarea></label>
    <div class="md:col-span-2"><button class="term-btn term-btn-sm">Save provider</button></div>
</form>
</div>
@endsection
