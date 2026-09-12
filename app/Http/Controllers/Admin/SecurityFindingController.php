<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityFinding;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class SecurityFindingController extends Controller
{
    public function index(Request $request)
    {
        $query = SecurityFinding::with('assignee')->latest();
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->boolean('kev')) {
            $query->where('is_known_exploited', true);
        }
        if ($request->filled('search')) {
            $s = addcslashes($request->search, '%_\\');
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('cve', 'like', "%{$s}%")
                  ->orWhere('affected_asset', 'like', "%{$s}%");
            });
        }
        $findings = $query->paginate(20)->withQueryString();
        $stats = [
            'open' => SecurityFinding::open()->count(),
            'kev' => SecurityFinding::open()->kev()->count(),
            'critical' => SecurityFinding::open()->forSeverity('critical')->count(),
        ];
        return view('admin.security.index', compact('findings', 'stats'));
    }

    public function create()
    {
        $staff = User::staff()->active()->orderBy('name')->get();
        return view('admin.security.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['reported_by'] = auth()->id();
        $finding = SecurityFinding::create($data);
        AuditService::log('create', 'security_findings', $finding, "Security finding {$finding->finding_number} opened.");
        return redirect()->route('admin.security-findings.show', $finding)->with('success', 'Finding recorded.');
    }

    public function show(SecurityFinding $finding)
    {
        $finding->load('assignee', 'reporter');
        return view('admin.security.show', compact('finding'));
    }

    public function edit(SecurityFinding $finding)
    {
        $staff = User::staff()->active()->orderBy('name')->get();
        return view('admin.security.edit', compact('finding', 'staff'));
    }

    public function update(Request $request, SecurityFinding $finding)
    {
        $oldStatus = $finding->status;
        $data = $this->validated($request);
        $finding->update($data);
        if (in_array($data['status'], ['resolved', 'verified'], true) && !$finding->resolved_at) {
            $finding->update(['resolved_at' => now()]);
        }
        if ($data['status'] === 'verified') {
            $finding->update(['verified_at' => now()]);
        }
        if ($oldStatus !== $data['status']) {
            AuditService::log('update', 'security_findings', $finding->fresh(), "Finding {$finding->finding_number}: {$oldStatus} → {$data['status']}.");
        }
        return redirect()->route('admin.security-findings.show', $finding)->with('success', 'Finding updated.');
    }

    public function destroy(SecurityFinding $finding)
    {
        $finding->delete();
        AuditService::log('delete', 'security_findings', $finding, "Finding {$finding->finding_number} archived.");
        return redirect()->route('admin.security-findings.index')->with('success', 'Finding archived.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'cve' => ['nullable', 'string', 'max:32', 'regex:/^(CVE-\d{4}-\d{4,}|)$/i'],
            'severity' => 'required|in:critical,high,medium,low,info',
            'cvss_score' => 'nullable|numeric|min:0|max:10',
            'cvss_vector' => 'nullable|string|max:128',
            'cvss_version' => 'nullable|string|max:8',
            'epss_score' => 'nullable|numeric|min:0|max:1',
            'is_known_exploited' => 'boolean',
            'affected_asset' => 'nullable|string|max:255',
            'affected_version' => 'nullable|string|max:64',
            'mitre_technique' => ['nullable', 'string', 'max:16', 'regex:/^(T\d{4}(\.\d{3})?|)$/'],
            'status' => 'required|in:open,triaged,in_progress,mitigated,resolved,verified,accepted_risk,false_positive',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:today',
            'evidence' => 'nullable|string|max:10000',
            'remediation' => 'nullable|string|max:10000',
            'discovered_source' => 'nullable|string|max:64',
        ]) + [
            'is_known_exploited' => $request->boolean('is_known_exploited'),
        ];
    }
}
