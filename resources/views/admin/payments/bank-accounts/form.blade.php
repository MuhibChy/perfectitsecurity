@extends('layouts.app')
@section('title', ($account->exists ? 'Edit' : 'Add') . ' Bank Account')
@section('page-title', 'Bank Accounts')

@section('content')
<x-page-header sys="PAYMENTS://BANK-ACCOUNTS" title="{{ $account->exists ? 'Edit account' : 'Add account' }}" subtitle="Blank account-number keeps the stored (encrypted) number." :breadcrumbs="['Bank accounts' => route('admin.bank-accounts.index'), ($account->exists ? 'Edit' : 'Add') => null]" />

<div class="term-panel p-4 max-w-3xl">
<form method="POST" action="{{ $account->exists ? route('admin.bank-accounts.update', $account) : route('admin.bank-accounts.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
    @csrf
    @if($account->exists) @method('PUT') @endif
    <label class="block">Label<input name="label" value="{{ old('label', $account->label) }}" class="term-input w-full" required placeholder="bKash-era collections — EBL"></label>
    <label class="block">Country<input name="country" value="{{ old('country', $account->country) }}" class="term-input w-full" required></label>
    <label class="block">Currency (ISO)<input name="currency" value="{{ old('currency', $account->currency) }}" class="term-input w-full font-mono" required maxlength="3"></label>
    <label class="block">Purpose<select name="purpose" class="term-input w-full">@foreach(['collections','payroll','general'] as $p)<option value="{{ $p }}" @selected(old('purpose', $account->purpose) === $p)>{{ ucfirst($p) }}</option>@endforeach</select></label>
    <label class="block">Account name<input name="account_name" value="{{ old('account_name', $account->account_name) }}" class="term-input w-full" required></label>
    <label class="block">Bank name<input name="bank_name" value="{{ old('bank_name', $account->bank_name) }}" class="term-input w-full" required></label>
    <label class="block">Branch<input name="branch" value="{{ old('branch', $account->branch) }}" class="term-input w-full"></label>
    <label class="block">Account number (encrypted)@if($account->exists)<span class="text-xs text-slate-500"> — stored: {{ $account->maskedNumber() }}</span>@endif<input name="account_number" class="term-input w-full font-mono" placeholder="leave blank to keep"></label>
    <label class="block">Routing number<input name="routing_number" value="{{ old('routing_number', $account->routing_number) }}" class="term-input w-full font-mono"></label>
    <label class="block">SWIFT / BIC<input name="swift_bic" value="{{ old('swift_bic', $account->swift_bic) }}" class="term-input w-full font-mono"></label>
    <label class="block">IBAN<input name="iban" value="{{ old('iban', $account->iban) }}" class="term-input w-full font-mono"></label>
    <label class="block">Sort order<input type="number" name="sort_order" value="{{ old('sort_order', $account->sort_order) }}" class="term-input w-full"></label>
    <label class="block md:col-span-2">Payment instructions (shown to customer)<textarea name="payment_instructions" rows="3" class="term-input w-full">{{ old('payment_instructions', $account->payment_instructions) }}</textarea></label>
    <label class="block">Active<select name="is_active" class="term-input w-full"><option value="1" @selected(old('is_active', $account->is_active))>Yes</option><option value="0" @selected(!old('is_active', $account->is_active))>No</option></select></label>
    <div class="md:col-span-2"><button class="term-btn term-btn-sm">Save account</button></div>
</form>
</div>
@endsection
