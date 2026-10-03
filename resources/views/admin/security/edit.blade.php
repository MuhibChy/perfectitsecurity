@extends('layouts.app')
@section('page-title', 'Edit Finding')
@section('content')

    <x-page-header title="Edit Finding" sys="SYSTEM://SECURITY" />
<div class="term-panel p-6 max-w-3xl">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Edit {{ $finding->finding_number }}</h2>
    <form method="POST" action="{{ route('admin.security-findings.update', $finding) }}" class="space-y-4">
        @csrf @method('PUT')
        <div><label class="term-field-label">Title *</label><input name="title" required class="term-input" value="{{ old('title', $finding->title) }}"></div>
        <div><label class="term-field-label">Description</label><textarea name="description" rows="3" class="term-input">{{ old('description', $finding->description) }}</textarea></div>
        <div class="grid sm:grid-cols-3 gap-4">
            <div><label class="term-field-label">CVE</label><input name="cve" maxlength="32" class="term-input" value="{{ old('cve', $finding->cve) }}"></div>
            <div><label class="term-field-label">Severity *</label><select name="severity" class="term-input">@foreach(['critical','high','medium','low','info'] as $s)<option value="{{ $s }}" @selected(old('severity', $finding->severity) === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Status *</label><select name="status" class="term-input">@foreach(['open','triaged','in_progress','mitigated','resolved','verified','accepted_risk','false_positive'] as $s)<option value="{{ $s }}" @selected(old('status', $finding->status) === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select></div>
            <div><label class="term-field-label">CVSS score</label><input name="cvss_score" type="number" step="0.1" min="0" max="10" class="term-input" value="{{ old('cvss_score', $finding->cvss_score) }}"></div>
            <div><label class="term-field-label">CVSS vector</label><input name="cvss_vector" maxlength="128" class="term-input" value="{{ old('cvss_vector', $finding->cvss_vector) }}"></div>
            <div><label class="term-field-label">CVSS version</label><input name="cvss_version" maxlength="8" class="term-input" value="{{ old('cvss_version', $finding->cvss_version) }}"></div>
            <div><label class="term-field-label">EPSS score</label><input name="epss_score" type="number" step="0.0001" min="0" max="1" class="term-input" value="{{ old('epss_score', $finding->epss_score) }}"></div>
            <div><label class="term-field-label">MITRE technique</label><input name="mitre_technique" maxlength="16" class="term-input" value="{{ old('mitre_technique', $finding->mitre_technique) }}"></div>
            <div><label class="term-field-label">Affected asset</label><input name="affected_asset" maxlength="255" class="term-input" value="{{ old('affected_asset', $finding->affected_asset) }}"></div>
            <div><label class="term-field-label">Affected version</label><input name="affected_version" maxlength="64" class="term-input" value="{{ old('affected_version', $finding->affected_version) }}"></div>
            <div><label class="term-field-label">Assign to</label><select name="assigned_to" class="term-input"><option value="">Unassigned</option>@foreach($staff as $u)<option value="{{ $u->id }}" @selected(old('assigned_to', $finding->assigned_to) == $u->id)>{{ $u->name }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Due date</label><input name="due_date" type="date" class="term-input" value="{{ old('due_date', $finding->due_date?->format('Y-m-d')) }}"></div>
            <div><label class="term-field-label">Discovery source</label><input name="discovered_source" maxlength="64" class="term-input" value="{{ old('discovered_source', $finding->discovered_source) }}"></div>
        </div>
        <div><label class="term-field-label">Evidence</label><textarea name="evidence" rows="3" class="term-input">{{ old('evidence', $finding->evidence) }}</textarea></div>
        <div><label class="term-field-label">Remediation plan</label><textarea name="remediation" rows="3" class="term-input">{{ old('remediation', $finding->remediation) }}</textarea></div>
        <div><label class="term-field-label"><input type="checkbox" name="is_known_exploited" value="1" @checked(old('is_known_exploited', $finding->is_known_exploited)) class="rounded"> Known exploited (CISA KEV)</label></div>
        <button class="term-btn">Save Changes</button>
    </form>
</div>
@endsection
