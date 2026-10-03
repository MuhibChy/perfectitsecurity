<?php

namespace App\Services;

use App\Models\ItsmChange;
use App\Models\KbArticle;
use App\Models\Problem;
use App\Models\ServiceApproval;
use App\Models\SlaBreachLog;
use App\Models\Ticket;
use App\Services\Ai\AiKnowledgeService;
use Illuminate\Support\Facades\DB;

/**
 * ITSM extension workflows (additive). All transitions are idempotent,
 * permission-checked by callers (route middleware + ownership scoping),
 * and audit-logged. No existing workflow is altered.
 */
class ItsmService
{
    /**
     * Record an SLA breach idempotently (one row per ticket+type).
     * Called from SlaService::processDeadlines — safe for scheduler retries.
     */
    public function recordBreach(Ticket $ticket, string $type): SlaBreachLog
    {
        return SlaBreachLog::firstOrCreate(
            ['ticket_id' => $ticket->id, 'breach_type' => $type],
            [
                'sla_policy_id' => $ticket->sla_policy_id,
                'deadline' => $type === 'response' ? $ticket->sla_response_deadline : $ticket->sla_resolution_deadline,
                'breached_at' => now(),
            ]
        );
    }

    /**
     * Transition a problem through its lifecycle with audit.
     */
    public function transitionProblem(Problem $problem, string $to, ?int $actorId = null, array $extra = []): Problem
    {
        $allowed = [
            'open' => ['investigating', 'closed'],
            'investigating' => ['known_error', 'resolved', 'open'],
            'known_error' => ['resolved', 'investigating'],
            'resolved' => ['closed', 'investigating'],
            'closed' => [],
        ];
        $from = $problem->status;
        if (!in_array($to, $allowed[$from] ?? [], true)) {
            abort(422, "Problem cannot move from {$from} to {$to}.");
        }

        return DB::transaction(function () use ($problem, $from, $to, $actorId, $extra) {
            $problem->update(array_merge($extra, [
                'status' => $to,
                'resolved_at' => $to === 'resolved' ? ($problem->resolved_at ?? now()) : $problem->resolved_at,
                'closed_at' => $to === 'closed' ? now() : $problem->closed_at,
            ]));
            AuditService::log('problem.transition', 'itsm', $problem->fresh(),
                "Problem {$problem->problem_number}: {$from} → {$to}", ['status' => $from], ['status' => $to]);
            return $problem->fresh();
        });
    }

    /**
     * Transition an ITIL change. High-risk changes require an approved
     * approval record before they can be scheduled (governance gate).
     */
    public function transitionChange(ItsmChange $change, string $to, ?int $actorId = null, array $extra = []): ItsmChange
    {
        $allowed = [
            'requested' => ['assessed', 'cancelled'],
            'assessed' => ['approved', 'cancelled', 'requested'],
            'approved' => ['scheduled', 'cancelled'],
            'scheduled' => ['implementing', 'cancelled'],
            'implementing' => ['completed', 'failed'],
            'failed' => ['assessed'],
            'completed' => [],
            'cancelled' => [],
        ];
        $from = $change->status;
        if (!in_array($to, $allowed[$from] ?? [], true)) {
            abort(422, "Change cannot move from {$from} to {$to}.");
        }
        if (in_array($to, ['scheduled', 'implementing', 'completed'], true) && $change->risk === 'high') {
            $approved = $change->approvals()->where('decision', 'approved')->exists()
                || $change->workflowApprovals()->where('status', 'approved')->exists();
            if (!$approved) {
                abort(422, 'High-risk changes require an approval before scheduling.');
            }
        }

        return DB::transaction(function () use ($change, $from, $to, $extra) {
            $change->update(array_merge($extra, [
                'status' => $to,
                'implemented_at' => $to === 'completed' ? now() : $change->implemented_at,
            ]));
            AuditService::log('change.transition', 'itsm', $change->fresh(),
                "Change {$change->change_number}: {$from} → {$to}", ['status' => $from], ['status' => $to]);
            return $change->fresh();
        });
    }

    /**
     * Generic approval decision (idempotent: only pending approvals change).
     */
    public function decideApproval(ServiceApproval $approval, string $decision, ?int $approverId, ?string $comments = null): ServiceApproval
    {
        if ($approval->status !== 'pending') {
            abort(422, 'This approval has already been decided.');
        }
        if (!in_array($decision, ['approved', 'rejected', 'cancelled'], true)) {
            abort(422, 'Invalid approval decision.');
        }

        return DB::transaction(function () use ($approval, $decision, $approverId, $comments) {
            $approval->update([
                'status' => $decision,
                'approver_id' => $approverId ?? $approval->approver_id,
                'comments' => $comments ?? $approval->comments,
                'decided_at' => now(),
            ]);
            AuditService::log('approval.decision', 'itsm', $approval->fresh(),
                "Approval {$approval->approval_number} {$decision}");
            return $approval->fresh();
        });
    }

    /**
     * Grounded assistance for a ticket: related open tickets (same category)
     * + permission-aware KB articles. Read-only; never mutates records, so
     * it cannot bypass permissions — callers still scope the ticket itself.
     */
    public function suggestForTicket(Ticket $ticket, $user, int $limit = 5): array
    {
        $relatedTickets = Ticket::query()
            ->where('id', '!=', $ticket->id)
            ->where('status', '!=', 'closed')
            ->when($ticket->category_id, fn ($q) => $q->where('category_id', $ticket->category_id))
            ->when($user && method_exists($user, 'isEmployee') && !$user->isEmployee() && !$user->isAdmin(),
                fn ($q) => $q->where('customer_id', $user->id))
            ->latest()->limit($limit)->get(['id', 'ticket_number', 'subject', 'status', 'priority']);

        try {
            $articles = app(AiKnowledgeService::class)
                ->searchRelevantArticles($ticket->subject . ' ' . ($ticket->description ?? ''), $user, $limit);
        } catch (\Throwable $e) {
            $articles = [];
        }
        if ($articles instanceof \Illuminate\Support\Collection) {
            $articles = $articles->values()->all();
        }

        return ['tickets' => $relatedTickets, 'articles' => $articles];
    }
}
