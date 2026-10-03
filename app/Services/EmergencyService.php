<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\EmergencyRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Emergency communication lane (audited triage).
 * new → acknowledged → assigned → in_progress → resolved → closed.
 * Creation is throttled at the route layer; CRITICAL notifies admins.
 */
class EmergencyService
{
    public function raise(User $requester, array $data): EmergencyRequest
    {
        $severity = strtoupper($data['severity'] ?? 'NORMAL');
        abort_unless(in_array($severity, EmergencyRequest::SEVERITIES, true), 422, 'Unknown severity.');
        abort_if(trim($data['description'] ?? '') === '', 422, 'Description is required.');

        return DB::transaction(function () use ($requester, $data, $severity) {
            $emergency = EmergencyRequest::create([
                'requester_id' => $requester->id,
                'severity' => $severity,
                'category' => $data['category'] ?? 'incident',
                'description' => $data['description'],
                'status' => 'new',
            ]);
            AuditLog::log('emergency.raised', 'emergency_requests', $emergency, "Emergency {$emergency->reference} raised by {$requester->name} [{$severity}].");
            if ($severity === 'CRITICAL') {
                foreach (User::whereIn('role', ['super_admin', 'admin', 'support_manager'])->where('is_active', true)->limit(10)->get() as $manager) {
                    ServiceTrackingService::notify((int) $manager->id, 'emergency_critical', "CRITICAL emergency {$emergency->reference}", mb_substr($emergency->description, 0, 140));
                }
            }

            return $emergency->fresh();
        });
    }

    public function transition(EmergencyRequest $emergency, string $to, User $actor, ?int $assigneeId = null): EmergencyRequest
    {
        abort_unless($actor->isStaff(), 403, 'Only staff can triage emergencies.');
        $allowed = [
            'new' => ['acknowledged', 'assigned'],
            'acknowledged' => ['assigned', 'in_progress'],
            'assigned' => ['in_progress', 'acknowledged'],
            'in_progress' => ['resolved'],
            'resolved' => ['closed', 'in_progress'],
            'closed' => [],
        ];
        abort_unless(in_array($to, $allowed[$emergency->status] ?? [], true), 422, "Cannot move emergency from {$emergency->status} to {$to}.");

        return DB::transaction(function () use ($emergency, $to, $actor, $assigneeId) {
            $emergency = EmergencyRequest::lockForUpdate()->findOrFail($emergency->id);
            $patch = ['status' => $to];
            if ($assigneeId) {
                abort_unless(User::where('id', $assigneeId)->first()?->isStaff(), 422, 'Assignee must be staff.');
                $patch['assignee_id'] = $assigneeId;
            }
            if ($to === 'acknowledged') {
                $patch['acknowledged_at'] = now();
            }
            if ($to === 'resolved') {
                $patch['resolved_at'] = now();
            }
            if ($to === 'closed') {
                $patch['closed_at'] = now();
            }
            $emergency->update($patch);
            AuditLog::log('emergency.transition', 'emergency_requests', $emergency, "Emergency {$emergency->reference}: {$emergency->getOriginal('status')} → {$to} by {$actor->name}.");
            ServiceTrackingService::notify((int) $emergency->requester_id, 'emergency_update', "Emergency {$emergency->reference}: {$to}", "Your emergency request is now {$to}.");

            return $emergency->fresh();
        });
    }
}
