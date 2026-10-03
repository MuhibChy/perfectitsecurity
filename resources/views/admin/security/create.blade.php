@extends('layouts.app')
@section('page-title', 'Record Finding')
@section('content')

    <x-page-header title="Record Finding" sys="SYSTEM://SECURITY" />
<div class="term-panel p-6 max-w-3xl">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Record Security Finding</h2>
    <form method="POST" action="{{ route('admin.security-findings.store') }}" class="space-y-4">
        @csrf
        <div><label class="term-field-label">Title *</label><input name="title" required class="term-input" value="{{ old('title') }}">@error('title')<p class="term-error">{{ $message }}</p>@enderror</div>
        <div><label class="term-field-label">Description</label><textarea name="description" rows="3" class="term-input">{{ old('description') }}</textarea></div>
        <div class="grid sm:grid-cols-3 gap-4">
            <div><label class="term-field-label">CVE (e.g. CVE-2024-12345)</label><input name="cve" maxlength="32" class="term-input" value="{{ old('cve') }}">@error('cve')<p class="term-error">{{ $message }}</p>@enderror</div>
            <div><label class="term-field-label">Severity *</label><select name="severity" class="term-input">@foreach(['critical','high','medium','low','info'] as $s)<option value="{{ $s }}" @selected(old('severity','medium') === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Status *</label><select name="status" class="term-input">@foreach(['open','triaged','in_progress','mitigated','resolved','verified','accepted_risk','false_positive'] as $s)<option value="{{ $s }}" @selected(old('status','open') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select></div>
            <div><label class="term-field-label">CVSS score (0–10)</label><input name="cvss_score" type="number" step="0.1" min="0" max="10" class="term-input" value="{{ old('cvss_score') }}">@error('cvss_score')<p class="term-error">{{ $message }}</p>@enderror</div>
            <div><label class="term-field-label">CVSS vector</label><input name="cvss_vector" maxlength="128" class="term-input" value="{{ old('cvss_vector') }}"></div>
            <div><label class="term-field-label">CVSS version</label><input name="cvss_version" maxlength="8" class="term-input" placeholder="3.1" value="{{ old('cvss_version') }}"></div>
            <div><label class="term-field-label">EPSS score (0–1, leave blank if unavailable)</label><input name="epss_score" type="number" step="0.0001" min="0" max="1" class="term-input" value="{{ old('epss_score') }}">@error('epss_score')<p class="term-error">{{ $message }}</p>@enderror</div>
            <div><label class="term-field-label">MITRE technique (e.g. T1595)</label><input name="mitre_technique" maxlength="16" class="term-input" value="{{ old('mitre_technique') }}">@error('mitre_technique')<p class="term-error">{{ $message }}</p>@enderror</div>
            <div><label class="term-field-label">Affected asset</label><input name="affected_asset" maxlength="255" class="term-input" value="{{ old('affected_asset') }}"></div>
            <div><label class="term-field-label">Affected version</label><input name="affected_version" maxlength="64" class="term-input" value="{{ old('affected_version') }}"></div>
            <div><label class="term-field-label">Assign to</label><select name="assigned_to" class="term-input"><option value="">Unassigned</option>@foreach($staff as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Due date</label><input name="due_date" type="date" class="term-input" value="{{ old('due_date') }}">@error('due_date')<p class="term-error">{{ $message }}</p>@enderror</div>
            <div><label class="term-field-label">Discovery source</label><input name="discovered_source" maxlength="64" class="term-input" placeholder="composer audit / pentest / report" value="{{ old('discovered_source') }}"></div>
        </div>
        <div><label class="term-field-label">Evidence</label><textarea name="evidence" rows="3" class="term-input">{{ old('evidence') }}</textarea></div>
        <div><label class="term-field-label">Remediation plan</label><textarea name="remediation" rows="3" class="term-input">{{ old('remediation') }}</textarea></div>
        <div><label class="term-field-label"><input type="checkbox" name="is_known_exploited" value="1" class="rounded"> Known exploited vulnerability (CISA KEV)</label></div>
        <button class="term-btn">Save Finding</button>
    </form>
</div>
@endsection
