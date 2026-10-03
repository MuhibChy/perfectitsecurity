@extends('layouts.app')
@section('title', 'Payment Configuration — Settings')
@section('page-title', 'Payment Settings')

@section('content')
<x-page-header sys="SYSTEM://SETTINGS/PAYMENTS" title="Payment System Settings" subtitle="Configure multi-provider payment behaviour, Bangladesh rails, international gateways, fee rules, and security." :breadcrumbs="['Settings' => route('admin.settings.index'), 'Payments' => null]">
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.payment-providers.index') }}" class="term-btn term-btn-ghost term-btn-sm">
            Providers Registry &rarr;
        </a>
        <a href="{{ route('admin.bank-accounts.index') }}" class="term-btn term-btn-ghost term-btn-sm">
            Bank Accounts &rarr;
        </a>
    </div>
</x-page-header>

<div class="max-w-6xl space-y-6">
    {{-- Quick Status Strip --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="term-panel p-4">
            <div class="term-field-label">Active Providers</div>
            <div class="text-2xl font-mono font-bold mt-1 text-slate-900 dark:text-white">
                {{ $providers->where('is_active', true)->count() }} <span class="text-xs text-slate-500 font-normal">/ {{ $providers->count() }}</span>
            </div>
        </div>
        <div class="term-panel p-4">
            <div class="term-field-label">Bangladesh Rails</div>
            <div class="text-2xl font-mono font-bold mt-1 text-emerald-600 dark:text-emerald-400">
                {{ $providers->whereIn('key', ['bkash', 'nagad', 'rocket', 'bank_transfer'])->where('is_active', true)->count() }}
            </div>
        </div>
        <div class="term-panel p-4">
            <div class="term-field-label">Company Bank Accounts</div>
            <div class="text-2xl font-mono font-bold mt-1 text-slate-900 dark:text-white">
                {{ $bankAccounts->where('is_active', true)->count() }}
            </div>
        </div>
        <div class="term-panel p-4">
            <div class="term-field-label">Credentials At Rest</div>
            <div class="text-sm font-mono font-bold mt-2 text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> AES-256 Encrypted
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.payments.update') }}" class="space-y-6">
        @csrf

        {{-- 1. General Section --}}
        <div class="term-panel p-6">
            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-200 dark:border-white/10">
                <span class="font-mono text-xs text-accent-soft font-bold uppercase tracking-wider">01 // GENERAL CONFIGURATION</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-800 dark:text-slate-200">Default Currency (ISO)</label>
                    <input type="text" name="payments_default_currency" value="{{ $settings['payments_default_currency'] ?? 'USD' }}" class="term-input w-full font-mono uppercase" maxlength="3" required placeholder="USD">
                    <p class="text-xs text-slate-500 mt-1">Default billing currency for invoices and services.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-800 dark:text-slate-200">Supported Currencies</label>
                    <input type="text" name="payments_supported_currencies" value="{{ $settings['payments_supported_currencies'] ?? 'BDT,GBP,USD,EUR' }}" class="term-input w-full font-mono uppercase" placeholder="BDT,GBP,USD,EUR">
                    <p class="text-xs text-slate-500 mt-1">Comma-separated ISO codes supported by the platform.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-800 dark:text-slate-200">Payment Checkout Timeout (Minutes)</label>
                    <input type="number" name="payments_timeout_minutes" value="{{ $settings['payments_timeout_minutes'] ?? '60' }}" class="term-input w-full font-mono" min="5" max="1440">
                    <p class="text-xs text-slate-500 mt-1">Maximum lifetime of an uncompleted checkout session.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-800 dark:text-slate-200">Invoice Payment Deadline (Days)</label>
                    <input type="number" name="payments_invoice_deadline_days" value="{{ $settings['payments_invoice_deadline_days'] ?? '14' }}" class="term-input w-full font-mono" min="1" max="365">
                    <p class="text-xs text-slate-500 mt-1">Default due period assigned to newly issued invoices.</p>
                </div>
            </div>
        </div>

        {{-- 2. Bangladesh Payment Methods --}}
        <div class="term-panel p-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-200 dark:border-white/10">
                <span class="font-mono text-xs text-accent-soft font-bold uppercase tracking-wider">02 // BANGLADESH PAYMENT METHODS (BDT)</span>
                <span class="text-xs text-slate-500">Official Merchant & Gateway APIs</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $bdKeys = ['bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket (DBBL)', 'bank_transfer' => 'Bank Transfer'];
                @endphp
                @foreach($bdKeys as $key => $title)
                    @php $prov = $providers->firstWhere('key', $key); @endphp
                    <div class="border border-slate-200 dark:border-white/10 rounded-lg p-4 bg-slate-50/50 dark:bg-white/[0.02] flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-sm text-slate-900 dark:text-white">{{ $title }}</span>
                                <span class="term-tag {{ $prov?->is_active ? 'term-tag-accent' : '' }}">
                                    {{ $prov?->is_active ? ($prov->isLive() ? '🔴 LIVE' : '🟡 TEST') : 'OFF' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mb-3">
                                @if($key === 'bkash') Official Tokenized Checkout & Merchant API
                                @elseif($key === 'nagad') Official Merchant Payment Gateway
                                @elseif($key === 'rocket') DBBL Merchant / Gateway Adapter
                                @else Manual Bank Transfer & Statement Verification
                                @endif
                            </p>
                        </div>
                        <div class="pt-2 border-t border-slate-200 dark:border-white/5 flex items-center justify-between text-xs">
                            <span class="font-mono text-slate-400">BDT</span>
                            @if($prov)
                            <a href="{{ route('admin.payment-providers.edit', $prov) }}" class="link-arrow font-medium text-primary-600 dark:text-accent-soft">Configure &rarr;</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 pt-3 border-t border-slate-200 dark:border-white/5 flex items-center justify-between text-xs text-slate-500">
                <span>Configured BD Bank Accounts: {{ $bankAccounts->where('country', 'Bangladesh')->count() }}</span>
                <a href="{{ route('admin.bank-accounts.index') }}" class="link-arrow text-primary-600 dark:text-accent-soft">Manage BD Bank Accounts &rarr;</a>
            </div>
        </div>

        {{-- 3. International Payment System --}}
        <div class="term-panel p-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-200 dark:border-white/10">
                <span class="font-mono text-xs text-accent-soft font-bold uppercase tracking-wider">03 // INTERNATIONAL PAYMENT SYSTEM</span>
                <span class="text-xs text-slate-500">Multi-Provider (GBP, USD, EUR)</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                    $intlKeys = [
                        'stripe' => ['title' => 'Stripe / Card Gateway', 'desc' => 'Hosted checkout, card tokenization, direct intents'],
                        'paypal' => ['title' => 'PayPal Gateway', 'desc' => 'PayPal wallet & international merchant payments'],
                        'international_gateway' => ['title' => 'International Gateway', 'desc' => 'Adyen, 2Checkout, Checkout.com, or wire gateway'],
                    ];
                @endphp
                @foreach($intlKeys as $key => $info)
                    @php $prov = $providers->firstWhere('key', $key); @endphp
                    <div class="border border-slate-200 dark:border-white/10 rounded-lg p-4 bg-slate-50/50 dark:bg-white/[0.02] flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-sm text-slate-900 dark:text-white">{{ $info['title'] }}</span>
                                <span class="term-tag {{ $prov?->is_active ? 'term-tag-accent' : '' }}">
                                    {{ $prov?->is_active ? ($prov->isLive() ? '🔴 LIVE' : '🟡 TEST') : 'OFF' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mb-3">{{ $info['desc'] }}</p>
                        </div>
                        <div class="pt-2 border-t border-slate-200 dark:border-white/5 flex items-center justify-between text-xs">
                            <span class="font-mono text-slate-400">GBP · USD · EUR</span>
                            @if($prov)
                            <a href="{{ route('admin.payment-providers.edit', $prov) }}" class="link-arrow font-medium text-primary-600 dark:text-accent-soft">Configure &rarr;</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 pt-3 border-t border-slate-200 dark:border-white/5 flex items-center justify-between text-xs text-slate-500">
                <span>International Wire / SWIFT Bank Accounts: {{ $bankAccounts->where('country', '!=', 'Bangladesh')->count() }}</span>
                <a href="{{ route('admin.bank-accounts.create') }}" class="link-arrow text-primary-600 dark:text-accent-soft">+ Add International Account &rarr;</a>
            </div>
        </div>

        {{-- 4. Fees Section --}}
        <div class="term-panel p-6">
            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-200 dark:border-white/10">
                <span class="font-mono text-xs text-accent-soft font-bold uppercase tracking-wider">04 // TRANSACTION FEES CONFIGURATION</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-800 dark:text-slate-200">Default Provider Fee (%)</label>
                    <input type="number" step="0.01" name="payments_default_provider_fee_percent" value="{{ $settings['payments_default_provider_fee_percent'] ?? '0' }}" class="term-input w-full font-mono" min="0" max="100">
                    <p class="text-xs text-slate-500 mt-1">Default estimated provider fee deducted from gross income for accounting.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-800 dark:text-slate-200">Default Platform Fee (%)</label>
                    <input type="number" step="0.01" name="payments_platform_fee_percent" value="{{ $settings['payments_platform_fee_percent'] ?? '0' }}" class="term-input w-full font-mono" min="0" max="100">
                    <p class="text-xs text-slate-500 mt-1">Platform service surcharge if configured by enterprise billing policy.</p>
                </div>
            </div>
        </div>

        {{-- 5. Refunds Section --}}
        <div class="term-panel p-6">
            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-200 dark:border-white/10">
                <span class="font-mono text-xs text-accent-soft font-bold uppercase tracking-wider">05 // REFUNDS & APPROVAL WORKFLOW</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-800 dark:text-slate-200">Approval Requirement</label>
                    <select name="payments_refund_require_approval" class="term-input w-full">
                        <option value="1" @selected(($settings['payments_refund_require_approval'] ?? '1') == '1')>Strict Finance Approval Required (Recommended)</option>
                        <option value="0" @selected(($settings['payments_refund_require_approval'] ?? '1') == '0')>Direct Execution (Restricted)</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">When enabled, refunds must pass through REQUESTED &rarr; APPROVED &rarr; EXECUTED.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-800 dark:text-slate-200">Customer Refund Requests</label>
                    <select name="payments_refund_allow_customer_request" class="term-input w-full">
                        <option value="1" @selected(($settings['payments_refund_allow_customer_request'] ?? '1') == '1')>Allow Customer In-Portal Refund Requests</option>
                        <option value="0" @selected(($settings['payments_refund_allow_customer_request'] ?? '1') == '0')>Admin / Finance Initiated Only</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Customers can request refunds from their Payment History tab.</p>
                </div>
            </div>
        </div>

        {{-- 6. Security Section --}}
        <div class="term-panel p-6">
            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-200 dark:border-white/10">
                <span class="font-mono text-xs text-accent-soft font-bold uppercase tracking-wider">06 // PAYMENT SECURITY & SECRETS CONFIGURATION</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-800 dark:text-slate-200">Webhook Signature Enforcement</label>
                    <select name="payments_require_webhook_secret" class="term-input w-full">
                        <option value="1" @selected(($settings['payments_require_webhook_secret'] ?? '1') == '1')>Enforce HMAC Signature on All Live Webhooks</option>
                        <option value="0" @selected(($settings['payments_require_webhook_secret'] ?? '1') == '0')>Permissive (Test Environment Only)</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Rejects unsigned or forged incoming provider webhook events.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-800 dark:text-slate-200">HTTPS Transport Security</label>
                    <select name="payments_force_https" class="term-input w-full">
                        <option value="1" @selected(($settings['payments_force_https'] ?? '1') == '1')>Mandatory HTTPS for Payment Endpoints</option>
                        <option value="0" @selected(($settings['payments_force_https'] ?? '1') == '0')>Allow HTTP (Local Development Only)</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Guards against eavesdropping and MITM token theft.</p>
                </div>
            </div>
            <div class="mt-4 p-3 rounded bg-slate-100 dark:bg-white/[0.03] border border-slate-200 dark:border-white/5 text-xs text-slate-600 dark:text-slate-400">
                <strong class="text-slate-900 dark:text-white">Credentials Security Notice:</strong>
                All provider API keys, secret keys, public tokens, and webhook secrets are encrypted at rest using Laravel's application cipher (<code class="font-mono text-accent-soft">APP_KEY</code>). Sensitive bank account numbers are masked (<code class="font-mono">****1234</code>) in the UI. Raw card numbers, CVVs, and customer PINs are NEVER stored on this platform.
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.settings.index') }}" class="term-btn term-btn-ghost">Cancel</a>
            <button type="submit" class="term-btn">Save Payment Settings</button>
        </div>
    </form>
</div>
@endsection
