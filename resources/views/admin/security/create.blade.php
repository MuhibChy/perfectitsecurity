@extends('layouts.app')
@section('page-title', 'Record Finding')
@section('content')
<div class="glass-card p-6 max-w-3xl">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Record Security Finding</h2>
    <form method="POST" action="{{ route('admin.security-findings.store') }}" class="space-y-4">
        @csrf
        <div><label class="form-label">Title *</label><input name="title" required class="form-input w-full" value="{{ old('title') }}">@error('title')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div><label class="form-label">Description</label><textarea name="description" rows="3" class="form-input w-full">{{ old('description') }}</textarea></div>
        <div class="grid sm:grid-cols-3 gap-4">
            <div><label class="form-label">CVE (e.g. CVE-2024-12345)</label><input name="cve" maxlength="32" class="form-input w-full" value="{{ old('cve') }}">@error('cve')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div><label class="form-label">Severity *</label><select name="severity" class="form-input w-full">@foreach(['critical','high','medium','low','info'] as $s)<option value="{{ $s }}" @selected(old('severity','medium') === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div><label class="form-label">Status *</label><select name="status" class="form-input w-full">@foreach(['open','triaged','in_progress','mitigated','resolved','verified','accepted_risk','false_positive'] as $s)<option value="{{ $s }}" @selected(old('status','open') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select></div>
            <div><label class="form-label">CVSS score (0–10)</label><input name="cvss_score" type="number" step="0.1" min="0" max="10" class="form-input w-full" value="{{ old('cvss_score') }}">@error('cvss_score')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div><label class="form-label">CVSS vector</label><input name="cvss_vector" maxlength="128" class="form-input w-full" value="{{ old('cvss_vector') }}"></div>
            <div><label class="form-label">CVSS version</label><input name="cvss_version" maxlength="8" class="form-input w-full" placeholder="3.1" value="{{ old('cvss_version') }}"></div>
            <div><label class="form-label">EPSS score (0–1, leave blank if unavailable)</label><input name="epss_score" type="number" step="0.0001" min="0" max="1" class="form-input w-full" value="{{ old('epss_score') }}">@error('epss_score')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div><label class="form-label">MITRE technique (e.g. T1595)</label><input name="mitre_technique" maxlength="16" class="form-input w-full" value="{{ old('mitre_technique') }}">@error('mitre_technique')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div><label class="form-label">Affected asset</label><input name="affected_asset" maxlength="255" class="form-input w-full" value="{{ old('affected_asset') }}"></div>
            <div><label class="form-label">Affected version</label><input name="affected_version" maxlength="64" class="form-input w-full" value="{{ old('affected_version') }}"></div>
            <div><label class="form-label">Assign to</label><select name="assigned_to" class="form-input w-full"><option value="">Unassigned</option>@foreach($staff as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></div>
            <div><label class="form-label">Due date</label><input name="due_date" type="date" class="form-input w-full" value="{{ old('due_date') }}">@error('due_date')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div><label class="form-label">Discovery source</label><input name="discovered_source" maxlength="64" class="form-input w-full" placeholder="composer audit / pentest / report" value="{{ old('discovered_source') }}"></div>
        </div>
        <div><label class="form-label">Evidence</label><textarea name="evidence" rows="3" class="form-input w-full">{{ old('evidence') }}</textarea></div>
        <div><label class="form-label">Remediation plan</label><textarea name="remediation" rows="3" class="form-input w-full">{{ old('remediation') }}</textarea></div>
        <div><label class="inline-flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="is_known_exploited" value="1" class="rounded"> Known exploited vulnerability (CISA KEV)</label></div>
        <button class="btn-primary">Save Finding</button>
    </form>
</div>
@endsection
