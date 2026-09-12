<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\SecurityFinding;
use App\Models\User;

class SecurityController extends Controller
{
    /** Internal security posture dashboard (admin only, never customers). */
    public function dashboard()
    {
        $since = now()->subDay();
        $data = [
            'findings_open' => SecurityFinding::open()->count(),
            'findings_kev' => SecurityFinding::open()->kev()->count(),
            'findings_critical' => SecurityFinding::open()->forSeverity('critical')->count(),
            'findings_by_severity' => SecurityFinding::open()
                ->selectRaw('severity, COUNT(*) as c')->groupBy('severity')->pluck('c', 'severity')->all(),
            'failed_logins_24h' => AuditLog::where('action', 'login_failed')->where('created_at', '>=', $since)->count(),
            'lockouts_24h' => AuditLog::where('action', 'login_locked')->where('created_at', '>=', $since)->count(),
            'privileged_accounts' => User::whereIn('role', ['super_admin', 'admin'])->where('is_active', true)->count(),
            'mfa_enrolled_staff' => User::staff()->where('is_active', true)->whereNotNull('two_factor_secret')->count(),
            'latest_backup' => Backup::latest()->first(),
            'recent_events' => AuditLog::whereIn('module', ['auth', 'security_findings', 'backups', 'payments', 'users'])
                ->latest()->limit(10)->get(),
        ];
        return view('admin.security.dashboard', compact('data'));
    }

    /** Software Bill of Materials from lockfiles (read-only inventory). */
    public function sbom()
    {
        $packages = [];
        $lockPath = base_path('composer.lock');
        if (is_readable($lockPath)) {
            $lock = json_decode(@file_get_contents($lockPath), true) ?: [];
            foreach (array_merge($lock['packages'] ?? [], $lock['packages-dev'] ?? []) as $pkg) {
                $packages[] = [
                    'name' => $pkg['name'] ?? '?',
                    'version' => $pkg['version'] ?? '?',
                    'license' => implode(', ', (array) ($pkg['license'] ?? ['unknown'])),
                    'dev' => isset($pkg['name']) && in_array($pkg['name'], array_column($lock['packages-dev'] ?? [], 'name'), true),
                    'source' => $pkg['source']['url'] ?? ($pkg['dist']['url'] ?? ''),
                ];
            }
        }
        $npm = [];
        $pkgPath = base_path('package.json');
        if (is_readable($pkgPath)) {
            $manifest = json_decode(@file_get_contents($pkgPath), true) ?: [];
            foreach (['dependencies' => false, 'devDependencies' => true] as $group => $dev) {
                foreach ($manifest[$group] ?? [] as $name => $version) {
                    $npm[] = ['name' => $name, 'version' => ltrim($version, '^~>=< '), 'dev' => $dev];
                }
            }
        }
        return view('admin.security.sbom', compact('packages', 'npm'));
    }
}
