<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Project;
use App\Models\ServiceEvent;
use App\Models\ServiceOrder;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * ServiceTrackingService — live service-tracking calculations and the
 * central service-event timeline. All figures derive from authoritative
 * rows; nothing is fabricated. Progress = average of milestone and task
 * completion (falls back to the controlled manual progress field).
 */
class ServiceTrackingService
{
    // ── event recording ────────────────────────────────────────
    public static function record(array $data): ServiceEvent
    {
        return ServiceEvent::create([
            'entity_type' => $data['entity_type'],
            'entity_id' => $data['entity_id'],
            'order_id' => $data['order_id'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'actor_id' => $data['actor_id'] ?? auth()->id(),
            'action' => $data['action'],
            'old_value' => $data['old'] ?? null,
            'new_value' => $data['new'] ?? null,
            'reason' => $data['reason'] ?? null,
            'comment' => $data['comment'] ?? null,
            'customer_visible' => $data['visible'] ?? false,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Central in-app notification writer with preference enforcement
     * (Phase 9). Before sending: resolve recipient + type, check their
     * NotificationPreference row. Disabled in-app → skipped (auditable via
     * return value). Emergency/critical types bypass ONLY when $force=true
     * is passed explicitly by the emergency lane (audited at call site).
     */
    public static function notify(int $userId, string $type, string $title, string $message, bool $force = false): bool
    {
        try {
            if (!$force) {
                $pref = \App\Models\NotificationPreference::where('user_id', $userId)->where('notification_type', $type)->first();
                if ($pref && !(bool) $pref->in_app_enabled) {
                    return false; // recipient disabled this channel: respect it
                }
            }
            Notification::create([
                'id' => (string) Str::uuid(),
                'type' => $type,
                'notifiable_type' => User::class,
                'notifiable_id' => $userId,
                'data' => json_encode(['title' => $title, 'message' => $message]),
                'read_at' => null,
            ]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ── progress (documented calculation) ──────────────────────
    public static function taskProgress(Task $task): int
    {
        if ($task->status === 'completed') return 100;
        if (in_array($task->status, ['cancelled', 'rejected'], true)) return 0;
        return max(0, min(100, (int) ($task->progress ?? 0)));
    }

    public static function projectProgress(Project $project): array
    {
        $milestones = $project->milestones;
        $tasks = $project->tasks;
        $parts = [];
        $basis = [];
        if ($milestones->count()) {
            $parts[] = $milestones->where('is_completed', true)->count() * 100 / $milestones->count();
            $basis[] = $milestones->count() . ' milestones';
        }
        if ($tasks->count()) {
            $done = $tasks->where('status', 'completed')->count();
            $parts[] = $done * 100 / $tasks->count();
            $basis[] = $tasks->count() . ' tasks';
        }
        if (empty($parts)) {
            return ['percent' => max(0, min(100, (int) $project->progress)), 'basis' => 'manual progress field'];
        }
        return ['percent' => (int) round(array_sum($parts) / count($parts)), 'basis' => 'calculated from ' . implode(' + ', $basis)];
    }

    // ── current stage ──────────────────────────────────────────
    public static function currentStage(Project $project): string
    {
        $waiting = ServiceEvent::where('project_id', $project->id)
            ->whereIn('action', ['waiting_for_customer', 'paused'])
            ->latest()->first();
        $resumed = ServiceEvent::where('project_id', $project->id)
            ->whereIn('action', ['resumed', 'started', 'progress_updated', 'completed'])
            ->latest()->first();
        if ($waiting && (!$resumed || $waiting->created_at > $resumed->created_at)) {
            return $waiting->action === 'paused' ? 'Paused' : 'Waiting for customer';
        }
        $ms = $project->milestones->where('is_completed', false)->sortBy('sort_order')->first();
        if ($ms) return $ms->name;
        $task = $project->tasks->whereIn('status', ['in_progress', 'submitted', 'under_review'])->sortByDesc('updated_at')->first();
        if ($task) return $task->title;
        return ucfirst(str_replace('_', ' ', $project->status));
    }

    // ── running time (from actual timestamps) ──────────────────
    public static function taskRunning(Task $task): ?array
    {
        $start = $task->start_date ?? $task->created_at;
        if (!$start) return null;
        $end = $task->completed_at;
        $frozen = false;
        if (!$end && $task->paused_at) {
            $end = $task->paused_at;
            $frozen = true;
        }
        $end = $end ?? now();
        $seconds = max(0, $end->diffInSeconds($start));
        return ['seconds' => $seconds, 'human' => self::humanDuration($seconds), 'frozen' => $frozen, 'started_at' => $start];
    }

    public static function humanDuration(int $seconds): string
    {
        $d = intdiv($seconds, 86400);
        $h = intdiv($seconds % 86400, 3600);
        $m = intdiv(($seconds % 3600), 60);
        if ($d > 0) return "{$d} days {$h} hours";
        if ($h > 0) return "{$h} hours {$m} min";
        return "{$m} min";
    }

    // ── ETA (deadline or honestly unknown) ─────────────────────
    public static function etaFor($entity): array
    {
        $deadline = $entity->deadline ?? $entity->preferred_date ?? null;
        if (!$deadline) return ['label' => 'Being assessed', 'date' => null];
        $deadline = $deadline instanceof \DateTimeInterface ? $deadline : \Carbon\Carbon::parse($deadline);
        return ['label' => $deadline->format('d M Y'), 'date' => $deadline];
    }

    public static function etaHistory(string $entityType, int $entityId): \Illuminate\Support\Collection
    {
        return ServiceEvent::where('entity_type', $entityType)->where('entity_id', $entityId)
            ->where('action', 'eta_changed')->with('actor')->latest()->get();
    }

    // ── live operations ────────────────────────────────────────
    public static function activeWork(): \Illuminate\Support\Collection
    {
        $tasks = Task::with(['project.customer', 'project.service', 'assignee', 'serviceOrder'])
            ->where('status', 'in_progress')->latest('updated_at')->take(100)->get();
        $waitingIds = ServiceEvent::whereIn('action', ['waiting_for_customer', 'paused'])
            ->selectRaw('MAX(id) as id')->groupBy('entity_type', 'entity_id')->pluck('id');
        $waiting = ServiceEvent::whereIn('id', $waitingIds)->where('entity_type', Task::class)->get()->keyBy('entity_id');
        $resumedIds = ServiceEvent::whereIn('action', ['resumed', 'started', 'progress_updated'])
            ->selectRaw('MAX(id) as id')->groupBy('entity_type', 'entity_id')->pluck('id');
        $resumed = ServiceEvent::whereIn('id', $resumedIds)->where('entity_type', Task::class)->get()->keyBy('entity_id');

        return $tasks->map(function ($t) use ($waiting, $resumed) {
            $w = $waiting->get($t->id);
            $r = $resumed->get($t->id);
            $isWaiting = $w && (!$r || $w->created_at > $r->created_at);
            $run = self::taskRunning($t);
            return [
                'task' => $t,
                'waiting' => $isWaiting ? $w : null,
                'running' => $run,
                'eta' => self::etaFor($t),
                'last_update' => $t->comments()->latest()->first(),
            ];
        });
    }

    public static function operationsStats(): array
    {
        $active = Task::where('status', 'in_progress')->count();
        $queued = Task::where('status', 'pending')->count();
        $waitingCustomers = ServiceEvent::where('action', 'waiting_for_customer')->where('created_at', '>=', now()->subDays(30))->distinct('entity_id')->count('entity_id');
        $overdue = Task::whereNotIn('status', ['completed', 'cancelled'])->whereNotNull('deadline')->whereDate('deadline', '<', today())->count();
        $completedToday = Task::where('status', 'completed')->whereDate('updated_at', today())->count();
        $updateRequests = ServiceEvent::whereIn('action', ['update_requested', 'query_asked'])->where('created_at', '>=', now()->subDays(14))->count();
        $maintenanceDue = \App\Models\ServiceMaintenance::whereIn('status', ['scheduled', 'active'])->whereDate('next_due_at', '<=', today()->addDays(14))->count();
        return compact('active', 'queued', 'waitingCustomers', 'overdue', 'completedToday', 'updateRequests', 'maintenanceDue');
    }

    // ── customer services rollup ───────────────────────────────
    public static function customerServices(User $customer): \Illuminate\Support\Collection
    {
        $orders = ServiceOrder::with(['service', 'tasks.project.milestones', 'tasks.project.tasks', 'invoices'])
            ->where('customer_id', $customer->id)->latest()->take(50)->get();
        return $orders->map(function ($o) {
            // Order→project bridge: task link first, recorded event link second.
            $project = $o->tasks->firstWhere('project_id', '!==', null)?->project;
            if (!$project) {
                $eventProjectId = ServiceEvent::where('order_id', $o->id)->whereNotNull('project_id')->latest()->first()?->project_id;
                $project = $eventProjectId ? Project::with(['milestones', 'tasks'])->find($eventProjectId) : null;
            }
            $progress = $project ? self::projectProgress($project) : ['percent' => 0, 'basis' => 'no project yet'];
            $lastUpdate = $project
                ? ServiceEvent::where('project_id', $project->id)->where('customer_visible', true)->latest()->first()
                : null;
            $due = round((float) $o->invoices->sum('amount_due'), 2);
            return [
                'order' => $o,
                'project' => $project,
                'progress' => $progress,
                'stage' => $project ? self::currentStage($project) : ucfirst(str_replace('_', ' ', $o->status)),
                'eta' => self::etaFor($project ?? $o),
                'last_update' => $lastUpdate,
                'due' => $due,
            ];
        });
    }

    public static function serviceTimeline(?int $orderId, ?int $projectId, bool $customerOnly = false): \Illuminate\Support\Collection
    {
        $q = ServiceEvent::with('actor')->latest();
        if ($orderId) $q->where('order_id', $orderId);
        if ($projectId) $q->where('project_id', $projectId);
        if ($customerOnly) $q->where('customer_visible', true);
        return $q->take(150)->get();
    }
}
