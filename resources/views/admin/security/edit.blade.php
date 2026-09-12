@extends('layouts.app')
@section('page-title', 'Edit Finding')
@section('content')
<div class="glass-card p-6 max-w-3xl">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Edit {{ $finding->finding_number }}</h2>
    <form method="POST" action="{{ route('admin.security-findings.update', $finding) }}" class="space-y-4">
        @csrf @method('PUT')
        <div><label class="form-label">Title *</label><input name="title" required class="form-input w-full" value="{{ old('title', $finding->title) }}"></div>
        <div><label class="form-label">Description</label><textarea name="description" rows="3" class="form-input w-full">{{ old('description', $finding->description) }}</textarea></div>
        <div class="grid sm:grid-cols-3 gap-4">
            <div><label class="form-label">CVE</label><input name="cve" maxlength="32" class="form-input w-full" value="{{ old('cve', $finding->cve) }}"></div>
            <div><label class="form-label">Severity *</label><select name="severity" class="form-input w-full">@foreach(['critical','high','medium','low','info'] as $s)<option value="{{ $s }}" @selected(old('severity', $finding->severity) === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div><label class="form-label">Status *</label><select name="status" class="form-input w-full">@foreach(['open','triaged','in_progress','mitigated','resolved','verified','accepted_risk','false_positive'] as $s)<option value="{{ $s }}" @selected(old('status', $finding->status) === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select></div>
            <div><label class="form-label">CVSS score</label><input name="cvss_score" type="number" step="0.1" min="0" max="10" class="form-input w-full" value="{{ old('cvss_score', $finding->cvss_score) }}"></div>
            <div><label class="form-label">CVSS vector</label><input name="cvss_vector" maxlength="128" class="form-input w-full" value="{{ old('cvss_vector', $finding->cvss_vector) }}"></div>
            <div><label class="form-label">CVSS version</label><input name="cvss_version" maxlength="8" class="form-input w-full" value="{{ old('cvss_version', $finding->cvss_version) }}"></div>
            <div><label class="form-label">EPSS score</label><input name="epss_score" type="number" step="0.0001" min="0" max="1" class="form-input w-full" value="{{ old('epss_score', $finding->epss_score) }}"></div>
            <div><label class="form-label">MITRE technique</label><input name="mitre_technique" maxlength="16" class="form-input w-full" value="{{ old('mitre_technique', $finding->mitre_technique) }}"></div>
            <div><label class="form-label">Affected asset</label><input name="affected_asset" maxlength="255" class="form-input w-full" value="{{ old('affected_asset', $finding->affected_asset) }}"></div>
            <div><label class="form-label">Affected version</label><input name="affected_version" maxlength="64" class="form-input w-full" value="{{ old('affected_version', $finding->affected_version) }}"></div>
            <div><label class="form-label">Assign to</label><select name="assigned_to" class="form-input w-full"><option value="">Unassigned</option>@foreach($staff as $u)<option value="{{ $u->id }}" @selected(old('assigned_to', $finding->assigned_to) == $u->id)>{{ $u->name }}</option>@endforeach</select></div>
            <div><label class="form-label">Due date</label><input name="due_date" type="date" class="form-input w-full" value="{{ old('due_date', $finding->due_date?->format('Y-m-d')) }}"></div>
            <div><label class="form-label">Discovery source</label><input name="discovered_source" maxlength="64" class="form-input w-full" value="{{ old('discovered_source', $finding->discovered_source) }}"></div>
        </div>
        <div><label class="form-label">Evidence</label><textarea name="evidence" rows="3" class="form-input w-full">{{ old('evidence', $finding->evidence) }}</textarea></div>
        <div><label class="form-label">Remediation plan</label><textarea name="remediation" rows="3" class="form-input w-full">{{ old('remediation', $finding->remediation) }}</textarea></div>
        <div><label class="inline-flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="is_known_exploited" value="1" @checked(old('is_known_exploited', $finding->is_known_exploited)) class="rounded"> Known exploited (CISA KEV)</label></div>
        <button class="btn-primary">Save Changes</button>
    </form>
</div>
@endsection
