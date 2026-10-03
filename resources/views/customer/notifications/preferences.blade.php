@extends('layouts.app')

@section('title', 'Notification Preferences — Customer Portal')
@section('page-title', 'Notification Preferences')

@section('content')
<div class="space-y-6">
    <x-page-header title="Notification Preferences" subtitle="Control how and when you receive notifications." sys="CLIENT://NOTIFY" :breadcrumbs="['Notifications' => route('portal.notifications.index'), 'Preferences' => null]" />

    <form action="{{ route('portal.notifications.preferences.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            {{-- Header Row --}}
            <div class="hidden sm:grid sm:grid-cols-3 gap-4 px-4 py-2 font-mono text-[11px] uppercase tracking-[0.18em] text-slate-600 dark:text-term-800">
                <div class="col-span-1">Type</div>
                <div class="text-center">Email</div>
                <div class="text-center">In-App</div>
            </div>

            {{-- Ticket Notifications --}}
            <div class="term-panel overflow-hidden">
                <div class="px-4 py-3 border-b border-white/10">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Support Tickets
                    </h3>
                </div>
                <div class="divide-y divide-white/5">
                    @foreach(['ticket_created', 'ticket_updated', 'ticket_reply'] as $type)
                    @php $pref = $preferences->get($type); @endphp
                    <div class="grid grid-cols-3 gap-4 px-4 py-3 items-center">
                        <div class="text-sm text-slate-600 dark:text-term-800">{{ $types[$type] }}</div>
                        <div class="text-center">
                            <input type="checkbox" name="email_{{ $type }}" value="1" {{ $pref && $pref->email_enabled ? 'checked' : '' }}
                                   class="w-4 h-4 accent-emerald-500">
                        </div>
                        <div class="text-center">
                            <input type="checkbox" name="in_app_{{ $type }}" value="1" {{ $pref && $pref->in_app_enabled ? 'checked' : ($pref ? '' : 'checked') }}
                                   class="w-4 h-4 accent-emerald-500">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Invoice Notifications --}}
            <div class="term-panel overflow-hidden">
                <div class="px-4 py-3 border-b border-white/10">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Invoices &amp; Billing
                    </h3>
                </div>
                <div class="divide-y divide-white/5">
                    @foreach(['invoice_created', 'invoice_paid', 'invoice_overdue'] as $type)
                    @php $pref = $preferences->get($type); @endphp
                    <div class="grid grid-cols-3 gap-4 px-4 py-3 items-center">
                        <div class="text-sm text-slate-600 dark:text-term-800">{{ $types[$type] }}</div>
                        <div class="text-center">
                            <input type="checkbox" name="email_{{ $type }}" value="1" {{ $pref && $pref->email_enabled ? 'checked' : '' }}
                                   class="w-4 h-4 accent-emerald-500">
                        </div>
                        <div class="text-center">
                            <input type="checkbox" name="in_app_{{ $type }}" value="1" {{ $pref && $pref->in_app_enabled ? 'checked' : ($pref ? '' : 'checked') }}
                                   class="w-4 h-4 accent-emerald-500">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Project Notifications --}}
            <div class="term-panel overflow-hidden">
                <div class="px-4 py-3 border-b border-white/10">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Projects &amp; Quotations
                    </h3>
                </div>
                <div class="divide-y divide-white/5">
                    @foreach(['project_updated', 'project_completed', 'quotation_received', 'quotation_approved'] as $type)
                    @php $pref = $preferences->get($type); @endphp
                    <div class="grid grid-cols-3 gap-4 px-4 py-3 items-center">
                        <div class="text-sm text-slate-600 dark:text-term-800">{{ $types[$type] }}</div>
                        <div class="text-center">
                            <input type="checkbox" name="email_{{ $type }}" value="1" {{ $pref && $pref->email_enabled ? 'checked' : '' }}
                                   class="w-4 h-4 accent-emerald-500">
                        </div>
                        <div class="text-center">
                            <input type="checkbox" name="in_app_{{ $type }}" value="1" {{ $pref && $pref->in_app_enabled ? 'checked' : ($pref ? '' : 'checked') }}
                                   class="w-4 h-4 accent-emerald-500">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- SLA & System --}}
            <div class="term-panel overflow-hidden">
                <div class="px-4 py-3 border-b border-white/10">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        SLA &amp; System
                    </h3>
                </div>
                <div class="divide-y divide-white/5">
                    @foreach(['sla_warning', 'sla_breach', 'system_announcement', 'marketing'] as $type)
                    @php $pref = $preferences->get($type); @endphp
                    <div class="grid grid-cols-3 gap-4 px-4 py-3 items-center">
                        <div class="text-sm text-slate-600 dark:text-term-800">{{ $types[$type] }}</div>
                        <div class="text-center">
                            <input type="checkbox" name="email_{{ $type }}" value="1" {{ $pref && $pref->email_enabled ? 'checked' : '' }}
                                   class="w-4 h-4 accent-emerald-500">
                        </div>
                        <div class="text-center">
                            <input type="checkbox" name="in_app_{{ $type }}" value="1" {{ $pref && $pref->in_app_enabled ? 'checked' : ($pref ? '' : ($type === 'marketing' ? '' : 'checked')) }}
                                   class="w-4 h-4 accent-emerald-500">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" class="term-btn">Save Preferences</button>
        </div>
    </form>
</div>
@endsection
