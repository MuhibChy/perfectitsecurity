<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SecurityFinding extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'is_demo',
        'finding_number', 'title', 'description', 'cve', 'severity',
        'cvss_score', 'cvss_vector', 'cvss_version', 'epss_score',
        'is_known_exploited', 'affected_asset', 'affected_version',
        'mitre_technique', 'status', 'assigned_to', 'reported_by',
        'due_date', 'evidence', 'remediation', 'discovered_source',
        'resolved_at', 'verified_at',
    ];

    protected $casts = [
        'is_demo' => 'boolean',
        'cvss_score' => 'decimal:1',
        'epss_score' => 'decimal:4',
        'is_known_exploited' => 'boolean',
        'due_date' => 'date',
        'resolved_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public const SEVERITIES = ['critical', 'high', 'medium', 'low', 'info'];
    public const STATUSES = ['open', 'triaged', 'in_progress', 'mitigated', 'resolved', 'verified', 'accepted_risk', 'false_positive'];

    protected static function booted()
    {
        static::creating(function ($finding) {
            if (empty($finding->finding_number)) {
                $finding->finding_number = 'SEC-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
    }

    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function reporter() { return $this->belongsTo(User::class, 'reported_by'); }

    public function scopeOpen($q) { return $q->whereNotIn('status', ['resolved', 'verified', 'accepted_risk', 'false_positive', 'closed']); }
    public function scopeKev($q) { return $q->where('is_known_exploited', true); }
    public function scopeForSeverity($q, $severity) { return $q->where('severity', $severity); }

    /** CVSS v3.x severity band derived from score (display helper, not stored). */
    public function cvssBand(): ?string
    {
        if ($this->cvss_score === null) return null;
        $s = (float) $this->cvss_score;
        return $s >= 9.0 ? 'critical' : ($s >= 7.0 ? 'high' : ($s >= 4.0 ? 'medium' : ($s > 0 ? 'low' : 'none')));
    }
}
