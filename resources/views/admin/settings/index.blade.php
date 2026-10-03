@extends('layouts.app')
@section('page-title', 'Settings Management')
@section('content')

    <x-page-header title="Settings Management" sys="SYSTEM://SETTINGS" />
<div class="term-panel p-6 mb-4">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Settings Management</h2>
    <p class="text-gray-500 dark:text-gray-400">Manage settings from this panel.</p>
</div>

<div class="term-panel p-6 mb-4 max-w-3xl">
    <h3 class="font-semibold mb-1">Company</h3>
    <form method="POST" action="{{ route('admin.settings.update') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @csrf
        <label class="block text-sm">Company name<input name="company_name" value="{{ $settings['company_name'] ?? '' }}" class="term-input w-full"></label>
        <label class="block text-sm">Company email<input name="company_email" type="email" value="{{ $settings['company_email'] ?? '' }}" class="term-input w-full"></label>
        <label class="block text-sm">Company phone<input name="company_phone" value="{{ $settings['company_phone'] ?? '' }}" class="term-input w-full"></label>
        <label class="block text-sm">Company website<input name="company_website" value="{{ $settings['company_website'] ?? '' }}" class="term-input w-full"></label>
        <label class="block text-sm md:col-span-2">Company address<textarea name="company_address" rows="2" class="term-input w-full">{{ $settings['company_address'] ?? '' }}</textarea></label>
        <div><button class="term-btn term-btn-sm">Save company</button></div>
    </form>
</div>

<div class="term-panel p-6 mb-4 max-w-3xl">
    <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold">Promotion campaign</h3>
        <a href="{{ route('admin.settings.promotion') }}" class="term-btn term-btn-sm text-xs font-semibold">
            Open Promotion Settings &rarr;
        </a>
    </div>
    <p class="text-xs text-slate-500">Limited-time service discount (e.g. 33% off eligible categories). Server-side enforced; historical quotations keep their snapshots.</p>
</div>

<div class="term-panel p-6 mb-4 max-w-3xl">
    <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold">Payments</h3>
        <a href="{{ route('admin.settings.payments') }}" class="term-btn term-btn-sm text-xs font-semibold">
            Open Dedicated Payment Settings Panel &rarr;
        </a>
    </div>
    <p class="text-xs text-slate-500 mb-3">General payment behaviour. Provider credentials live under <a class="link-arrow" href="{{ route('admin.payment-providers.index') }}">Payments → Providers</a> (encrypted); receiving accounts under <a class="link-arrow" href="{{ route('admin.bank-accounts.index') }}">Bank Accounts</a>. Configure all sections under <a class="link-arrow" href="{{ route('admin.settings.payments') }}">Settings → Payments</a>.</p>
    <form method="POST" action="{{ route('admin.settings.update') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @csrf
        <label class="block text-sm">Default currency (ISO)<input name="payments_default_currency" value="{{ $settings['payments_default_currency'] ?? '' }}" class="term-input w-full font-mono" maxlength="3" placeholder="USD"></label>
        <label class="block text-sm">Supported currencies (comma separated)<input name="payments_supported_currencies" value="{{ $settings['payments_supported_currencies'] ?? '' }}" class="term-input w-full font-mono" placeholder="BDT,GBP,USD,EUR"></label>
        <label class="block text-sm">Checkout timeout (minutes)<input type="number" name="payments_timeout_minutes" value="{{ $settings['payments_timeout_minutes'] ?? '' }}" class="term-input w-full" min="5" max="1440"></label>
        <label class="block text-sm">Invoice payment deadline (days)<input type="number" name="payments_invoice_deadline_days" value="{{ $settings['payments_invoice_deadline_days'] ?? '' }}" class="term-input w-full" min="1" max="365"></label>
        <label class="block text-sm">Default platform fee (%)<input type="number" step="0.01" name="payments_platform_fee_percent" value="{{ $settings['payments_platform_fee_percent'] ?? '' }}" class="term-input w-full" min="0" max="100"></label>
        <label class="block text-sm">Require webhook secrets<select name="payments_require_webhook_secret" class="term-input w-full"><option value="1" @selected(($settings['payments_require_webhook_secret'] ?? '1') == '1')>Yes (recommended)</option><option value="0" @selected(($settings['payments_require_webhook_secret'] ?? '1') == '0')>No (test only)</option></select></label>
        <div class="md:col-span-2"><button class="term-btn term-btn-sm">Save payments settings</button></div>
    </form>
</div>
@endsection
