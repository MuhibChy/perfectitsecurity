@extends('layouts.app')

@section('title', 'Notification Preferences — Customer Portal')
@section('page-title', 'Notification Preferences')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Control how and when you receive notifications.</p>
    </div>

    <form action="{{ route('portal.notifications.preferences.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            {{-- Header Row --}}
            <div class="hidden sm:grid sm:grid-cols-3 gap-4 px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                <div class="col-span-1">Notification Type</div>
                <div class="text-center">Email</div>
                <div class="text-center">In-App</div>
            </div>

            {{-- Ticket Notifications --}}
            <div class="rounded-xl border border-white/10 overflow-hidden">
                <div class="px-4 py-3 bg-white/[0.02] border-b border-white/5">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        Support Tickets
                    </h3>
                </div>
                <div class="divide-y divide-white/5">
                    @foreach(['ticket_created', 'ticket_updated', 'ticket_reply'] as $type)
                    @php $pref = $preferences->get($type); @endphp
                    <div class="grid grid-cols-3 gap-4 px-4 py-3 items-center">
                        <div class="text-sm text-gray-300">{{ $types[$type] }}</div>
                        <div class="text-center">
                            <input type="checkbox" name="email_{{ $type }}" value="1" {{ $pref && $pref->email_enabled ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-gray-800">
                        </div>
                        <div class="text-center">
                            <input type="checkbox" name="in_app_{{ $type }}" value="1" {{ $pref && $pref->in_app_enabled ? 'checked' : ($pref ? '' : 'checked') }}
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-gray-800">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Invoice Notifications --}}
            <div class="rounded-xl border border-white/10 overflow-hidden">
                <div class="px-4 py-3 bg-white/[0.02] border-b border-white/5">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        Invoices & Billing
                    </h3>
                </div>
                <div class="divide-y divide-white/5">
                    @foreach(['invoice_created', 'invoice_paid', 'invoice_overdue'] as $type)
                    @php $pref = $preferences->get($type); @endphp
                    <div class="grid grid-cols-3 gap-4 px-4 py-3 items-center">
                        <div class="text-sm text-gray-300">{{ $types[$type] }}</div>
                        <div class="text-center">
                            <input type="checkbox" name="email_{{ $type }}" value="1" {{ $pref && $pref->email_enabled ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-gray-800">
                        </div>
                        <div class="text-center">
                            <input type="checkbox" name="in_app_{{ $type }}" value="1" {{ $pref && $pref->in_app_enabled ? 'checked' : ($pref ? '' : 'checked') }}
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-gray-800">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Project Notifications --}}
            <div class="rounded-xl border border-white/10 overflow-hidden">
                <div class="px-4 py-3 bg-white/[0.02] border-b border-white/5">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Projects & Quotations
                    </h3>
                </div>
                <div class="divide-y divide-white/5">
                    @foreach(['project_updated', 'project_completed', 'quotation_received', 'quotation_approved'] as $type)
                    @php $pref = $preferences->get($type); @endphp
                    <div class="grid grid-cols-3 gap-4 px-4 py-3 items-center">
                        <div class="text-sm text-gray-300">{{ $types[$type] }}</div>
                        <div class="text-center">
                            <input type="checkbox" name="email_{{ $type }}" value="1" {{ $pref && $pref->email_enabled ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-gray-800">
                        </div>
                        <div class="text-center">
                            <input type="checkbox" name="in_app_{{ $type }}" value="1" {{ $pref && $pref->in_app_enabled ? 'checked' : ($pref ? '' : 'checked') }}
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-gray-800">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- SLA & System --}}
            <div class="rounded-xl border border-white/10 overflow-hidden">
                <div class="px-4 py-3 bg-white/[0.02] border-b border-white/5">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        SLA & System
                    </h3>
                </div>
                <div class="divide-y divide-white/5">
                    @foreach(['sla_warning', 'sla_breach', 'system_announcement', 'marketing'] as $type)
                    @php $pref = $preferences->get($type); @endphp
                    <div class="grid grid-cols-3 gap-4 px-4 py-3 items-center">
                        <div class="text-sm text-gray-300">{{ $types[$type] }}</div>
                        <div class="text-center">
                            <input type="checkbox" name="email_{{ $type }}" value="1" {{ $pref && $pref->email_enabled ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-gray-800">
                        </div>
                        <div class="text-center">
                            <input type="checkbox" name="in_app_{{ $type }}" value="1" {{ $pref && $pref->in_app_enabled ? 'checked' : ($pref ? '' : ($type === 'marketing' ? '' : 'checked')) }}
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-gray-800">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-colors text-sm font-semibold">Save Preferences</button>
        </div>
    </form>
</div>
@endsection
