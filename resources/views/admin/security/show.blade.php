@extends('layouts.app')
@section('page-title', 'Finding ' . $finding->finding_number)
@section('content')

    <x-page-header title="Security" sys="SYSTEM://SECURITY" />
<div class="term-panel p-6 max-w-3xl">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-xl font-bold">{{ $finding->title }}</h2>
        <a href="{{ route('admin.security-findings.edit', $finding) }}" class="term-btn term-btn-ghost term-btn-sm">Edit</a>
    </div>
    <p class="text-sm text-gray-500 mb-4">{{ $finding->finding_number }} · {{ $finding->severity }} · {{ str_replace('_',' ',$finding->status) }}
        @if($finding->is_known_exploited) · <strong>Known exploited</strong>@endif
    </p>
    <dl class="grid sm:grid-cols-2 gap-3 text-sm">
        <div><dt class="term-field-label">CVE</dt><dd class="font-mono">{{ $finding->cve ?? '—' }}</dd></div>
        <div><dt class="term-field-label">CVSS</dt><dd>{{ $finding->cvss_score ?? '—' }}{{ $finding->cvss_version ? " (v{$finding->cvss_version})" : '' }}@if($finding->cvssBand()) · band: {{ $finding->cvssBand() }}@endif</dd></div>
        <div class="sm:col-span-2"><dt class="term-field-label">CVSS vector</dt><dd class="font-mono text-xs break-all">{{ $finding->cvss_vector ?? '—' }}</dd></div>
        <div><dt class="term-field-label">EPSS</dt><dd>{{ $finding->epss_score ?? 'Not available' }}</dd></div>
        <div><dt class="term-field-label">MITRE ATT&amp;CK</dt><dd class="font-mono">{{ $finding->mitre_technique ?? '—' }}</dd></div>
        <div><dt class="term-field-label">Asset</dt><dd>{{ $finding->affected_asset ?? '—' }}{{ $finding->affected_version ? " ({$finding->affected_version})" : '' }}</dd></div>
        <div><dt class="term-field-label">Assignee / Due</dt><dd>{{ $finding->assignee?->name ?? '—' }} / {{ $finding->due_date ?? '—' }}</dd></div>
        <div><dt class="term-field-label">Reporter</dt><dd>{{ $finding->reporter?->name ?? '—' }}</dd></div>
        <div><dt class="term-field-label">Source</dt><dd>{{ $finding->discovered_source ?? '—' }}</dd></div>
    </dl>
    @if($finding->description)<h3 class="font-bold mt-4">Description</h3><div class="text-sm whitespace-pre-line">{{ $finding->description }}</div>@endif
    @if($finding->evidence)<h3 class="font-bold mt-4">Evidence</h3><div class="text-sm whitespace-pre-line">{{ $finding->evidence }}</div>@endif
    @if($finding->remediation)<h3 class="font-bold mt-4">Remediation</h3><div class="text-sm whitespace-pre-line">{{ $finding->remediation }}</div>@endif
    <form method="POST" action="{{ route('admin.security-findings.destroy', $finding) }}" class="mt-6" onsubmit="return confirm('Archive this finding?')">
        @csrf @method('DELETE')
        <button class="text-red-500 text-sm">Archive finding</button>
    </form>
</div>
@endsection
