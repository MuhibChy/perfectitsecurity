@extends('layouts.app')
@section('page-title', 'Security Dashboard')
@section('content')
<div class="glass-card p-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Security Posture Dashboard</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Internal overview for administrators. Aligned with NIST CSF (Govern/Identify/Protect/Detect/Respond/Recover) — alignment only, not certification.</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card title="Open Findings" :value="$data['findings_open']" color="blue" />
        <x-stat-card title="Known Exploited" :value="$data['findings_kev']" color="amber" />
        <x-stat-card title="Critical Open" :value="$data['findings_critical']" color="rose" />
        <x-stat-card title="Failed Logins (24h)" :value="$data['failed_logins_24h']" color="amber" />
        <x-stat-card title="Lockouts (24h)" :value="$data['lockouts_24h']" color="rose" />
        <x-stat-card title="Privileged Accounts" :value="$data['privileged_accounts']" color="blue" />
        <x-stat-card title="Staff with MFA" :value="$data['mfa_enrolled_staff']" color="emerald" />
        <x-stat-card title="Latest Backup" :value="$data['latest_backup'] ? ($data['latest_backup']->verification_status ?? $data['latest_backup']->status) : 'none'" color="emerald" />
    </div>
    <div class="grid lg:grid-cols-2 gap-6">
        <div>
            <h3 class="font-bold mb-2">Open findings by severity</h3>
            @forelse($data['findings_by_severity'] as $sev => $count)
                <div class="flex justify-between text-sm py-1 border-b border-gray-100 dark:border-gray-800"><span class="capitalize">{{ $sev }}</span><strong>{{ $count }}</strong></div>
            @empty
                <p class="text-sm text-gray-500">No open findings. Record them under Security Findings.</p>
            @endforelse
            <div class="mt-4 flex gap-2">
                <a href="{{ route('admin.security-findings.index') }}" class="btn-secondary btn-sm">Manage Findings</a>
                <a href="{{ route('admin.sbom') }}" class="btn-secondary btn-sm">SBOM Inventory</a>
            </div>
        </div>
        <div>
            <h3 class="font-bold mb-2">Recent security events</h3>
            <div class="space-y-2 text-sm">
                @forelse($data['recent_events'] as $e)
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="font-mono text-xs">{{ $e->action }}</span>
                        <span class="text-gray-500">{{ $e->module }} · {{ $e->created_at?->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-gray-500">No recent events.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
