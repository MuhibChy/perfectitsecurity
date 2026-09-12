@extends('layouts.app')
@section('page-title', 'Security Findings')
@section('content')
<div class="glass-card p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Vulnerability &amp; Security Findings</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">CVE-tracked findings with CVSS/EPSS scoring, KEV flagging, and remediation workflow. Admin-only.</p>
        </div>
        <a href="{{ route('admin.security-findings.create') }}" class="btn-primary btn-sm">Record Finding</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
        <x-stat-card title="Open Findings" :value="$stats['open']" color="blue" />
        <x-stat-card title="Known Exploited (open)" :value="$stats['kev']" color="amber" />
        <x-stat-card title="Critical (open)" :value="$stats['critical']" color="rose" />
    </div>
    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, CVE, asset…" class="form-input w-64">
        <select name="severity" class="form-input">
            <option value="">All severities</option>
            @foreach(['critical','high','medium','low','info'] as $s)
                <option value="{{ $s }}" @selected(request('severity') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="status" class="form-input">
            <option value="">All statuses</option>
            @foreach(['open','triaged','in_progress','mitigated','resolved','verified','accepted_risk','false_positive'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="kev" value="1" @checked(request()->boolean('kev')) class="rounded"> KEV only</label>
        <button class="btn-secondary btn-sm">Filter</button>
    </form>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm data-table">
            <thead><tr><th>Finding</th><th>CVE</th><th>Severity</th><th>CVSS</th><th>KEV</th><th>Status</th><th>Assignee</th><th>Updated</th></tr></thead>
            <tbody>
            @forelse($findings as $f)
                <tr>
                    <td><a href="{{ route('admin.security-findings.show', $f) }}" class="font-semibold text-primary-600">{{ $f->finding_number }} — {{ $f->title }}</a></td>
                    <td class="font-mono">{{ $f->cve ?? '—' }}</td>
                    <td><span class="badge">{{ $f->severity }}</span></td>
                    <td>{{ $f->cvss_score ?? '—' }}</td>
                    <td>{{ $f->is_known_exploited ? 'Yes' : '—' }}</td>
                    <td><span class="badge">{{ str_replace('_',' ',$f->status) }}</span></td>
                    <td>{{ $f->assignee?->name ?? '—' }}</td>
                    <td class="text-gray-500">{{ $f->updated_at?->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="py-8 text-center text-gray-500">No findings recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $findings->links() }}</div>
</div>
@endsection
