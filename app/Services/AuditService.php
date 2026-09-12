<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    /**
     * Static (no instance state; delegates to AuditLog) so both call styles
     * work: AuditService::log(...) and app(AuditService::class)->log(...).
     */
    public static function log(string $action, string $module, $model = null, string $description = null, array $old = null, array $new = null): AuditLog
    {
        return AuditLog::log($action, $module, $model, $description, $old, $new);
    }

    public static function getRecent(int $limit = 50)
    {
        return AuditLog::with('user')->latest()->limit($limit)->get();
    }

    public static function getForModel($model)
    {
        return AuditLog::where('auditable_type', get_class($model))
            ->where('auditable_id', $model->id)
            ->with('user')
            ->latest()
            ->get();
    }

    public static function getByModule(string $module, $from = null, $to = null)
    {
        $query = AuditLog::where('module', $module)->with('user')->latest();
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
        return $query->get();
    }
}
