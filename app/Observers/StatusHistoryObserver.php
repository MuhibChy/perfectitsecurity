<?php

namespace App\Observers;

use App\Models\AuditLog;

/**
 * Records status transitions into the existing audit log (no duplicate
 * system). Only the status field is captured — never sensitive values.
 * Failures are swallowed so business writes can never break.
 */
class StatusHistoryObserver
{
    protected const MODULES = [
        \App\Models\ServiceOrder::class => 'orders',
        \App\Models\Project::class => 'projects',
        \App\Models\Task::class => 'tasks',
        \App\Models\Ticket::class => 'tickets',
        \App\Models\Invoice::class => 'invoices',
        \App\Models\Quotation::class => 'quotations',
    ];

    public function updating($model): void
    {
        try {
            if (!$model->isDirty('status')) return;
            $old = $model->getOriginal('status');
            $new = $model->status;
            if ($old === $new) return;
            $module = self::MODULES[get_class($model)] ?? 'system';
            AuditLog::log(
                'status.changed', $module, $model,
                class_basename($model) . " status changed from '{$old}' to '{$new}'.",
                ['status' => $old], ['status' => $new]
            );
        } catch (\Throwable $e) {
            // Audit must never break business writes.
        }
    }
}
