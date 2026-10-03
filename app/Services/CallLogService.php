<?php

namespace App\Services;

use App\Contracts\CallProvider;
use App\Models\AuditLog;
use App\Models\CallLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Voice-communication records (§10). Permission rules mirror messaging:
 * customers reach staff only; staff may call anyone callable. Missed calls
 * notify the recipient; every record is audited. Customer visibility is
 * row-level (internal coordination calls stay staff-only).
 */
class CallLogService
{
    public const RELATED = [
        'ticket' => \App\Models\Ticket::class,
        'project' => \App\Models\Project::class,
        'order' => \App\Models\ServiceOrder::class,
    ];

    public function __construct(private CallProvider $provider) {}

    public function canCall(User $caller, User $recipient): bool
    {
        if ((int) $caller->id === (int) $recipient->id) return false;
        if ($caller->isCustomer()) return !$recipient->isCustomer();
        if ($caller->isFreelancer()) return $recipient->isStaff();
        return true;
    }

    public function log(User $actor, User $caller, User $recipient, array $data): CallLog
    {
        abort_unless($this->canCall($caller, $recipient), 403, 'You are not permitted to call this account.');

        $relatedType = $data['related_type'] ?? null;
        $relatedId = $data['related_id'] ?? null;
        if ($relatedType) {
            abort_unless(isset(self::RELATED[$relatedType]), 422, 'Unknown call link target.');
            self::RELATED[$relatedType]::findOrFail($relatedId);
        }
        // Customers can never create staff-only (hidden) call records.
        $visible = (bool) ($data['is_customer_visible'] ?? true);
        if ($caller->isCustomer() || $recipient->isCustomer()) $visible = true;

        return DB::transaction(function () use ($actor, $caller, $recipient, $data, $relatedType, $relatedId, $visible) {
            $log = CallLog::create([
                'caller_id' => $caller->id,
                'recipient_id' => $recipient->id,
                'direction' => $data['direction'] ?? 'outbound',
                'provider' => $this->provider->name(),
                'provider_call_id' => $data['provider_call_id'] ?? null,
                'started_at' => $data['started_at'] ?? now(),
                'duration_seconds' => (int) ($data['duration_seconds'] ?? 0),
                'outcome' => $data['outcome'] ?? 'completed',
                'related_type' => $relatedType ? self::RELATED[$relatedType] : null,
                'related_id' => $relatedId,
                'subject' => $data['subject'] ?? null,
                'notes' => $data['notes'] ?? null,
                'is_customer_visible' => $visible,
                'created_by' => $actor->id,
            ]);
            AuditLog::log('call.logged', 'call_logs', $log, "Call {$caller->name} → {$recipient->name} ({$log->outcome}, {$log->duration_seconds}s).");
            if ($log->outcome === 'missed') {
                ServiceTrackingService::notify((int) $recipient->id, 'call_missed', "Missed call from {$caller->name}", (string) ($log->subject ?? 'Call'));
            }
            return $log->fresh();
        });
    }

    public function forUser(User $viewer, ?User $other = null)
    {
        $q = CallLog::visibleTo($viewer)->with(['caller', 'recipient'])->latest();
        if ($other) {
            $q->where(fn ($w) => $w->where(fn ($x) => $x->where('caller_id', $viewer->id)->where('recipient_id', $other->id))
                ->orWhere(fn ($x) => $x->where('caller_id', $other->id)->where('recipient_id', $viewer->id)));
        }
        return $q->paginate(20);
    }
}
