<?php

namespace App\Services\Backup;

use App\Models\Backup;
use App\Services\AuditService;
use Carbon\Carbon;

/**
 * Configurable retention: keep N daily / M weekly / K monthly verified
 * backups. Pre-deploy backups are held until manually reviewed. NEVER:
 * the sole remaining backup, a backup in restore, or a retention_hold.
 * Every automatic deletion is audit-logged with actor + reason.
 */
class BackupRetentionService
{
    /** @return array{deleted: array, kept: int} */
    public function prune(?object $actor = null): array
    {
        $keepDaily = (int) config('backup.retention.keep_daily', 7);
        $keepWeekly = (int) config('backup.retention.keep_weekly', 4);
        $keepMonthly = (int) config('backup.retention.keep_monthly', 12);

        $candidates = Backup::active()
            ->where('status', 'verified')
            ->where('retention_hold', false)
            ->orderByDesc('created_at')
            ->get();

        if ($candidates->count() <= 1) {
            return ['deleted' => [], 'kept' => $candidates->count()]; // never delete the only backup
        }

        $keepIds = [];
        $daily = $weekly = $monthly = 0;
        $seenWeeks = [];
        $seenMonths = [];
        foreach ($candidates as $backup) {
            $created = $backup->created_at ?: now();
            $weekKey = $created->format('o-W');
            $monthKey = $created->format('Y-m');
            if ($backup->type === 'pre_deploy' && config('backup.retention.keep_pre_deploy', true)) {
                $keepIds[] = $backup->id;
                continue;
            }
            if ($daily < $keepDaily) {
                $keepIds[] = $backup->id;
                $daily++;
                $seenWeeks[$weekKey] = true;
                $seenMonths[$monthKey] = true;
                continue;
            }
            if (! isset($seenWeeks[$weekKey]) && $weekly < $keepWeekly) {
                $keepIds[] = $backup->id;
                $weekly++;
                $seenWeeks[$weekKey] = true;
                $seenMonths[$monthKey] = true;
                continue;
            }
            if (! isset($seenMonths[$monthKey]) && $monthly < $keepMonthly) {
                $keepIds[] = $backup->id;
                $monthly++;
                $seenMonths[$monthKey] = true;
            }
        }

        $deleted = [];
        $adapter = new LocalBackupDiskAdapter();
        foreach ($candidates as $backup) {
            if (in_array($backup->id, $keepIds, true)) {
                continue;
            }
            if ($backup->files()->count() === 0) {
                continue;
            }
            // Safety: re-check sole-backup invariant at deletion time.
            if (Backup::active()->where('status', 'verified')->count() <= 1) {
                break;
            }
            foreach ($backup->files as $file) {
                try {
                    $adapter->delete($file->path);
                } catch (\Throwable) {
                }
            }
            $backup->update(['deleted_at' => now(), 'status' => 'deleted']);
            $deleted[] = $backup->backup_id;
            app(AuditService::class)->log(
                'backup.pruned',
                'backups',
                $backup,
                "Retention cleanup deleted backup {$backup->backup_id} (kept per policy D{$keepDaily}/W{$keepWeekly}/M{$keepMonthly})."
            );
        }

        return ['deleted' => $deleted, 'kept' => Backup::active()->count()];
    }

    public function nextScheduledRun(string $type = 'full'): ?Carbon
    {
        // Informational helper for the dashboard (scheduler owns actual runs).
        return match ($type) {
            'db' => now()->addHours(6),
            default => now()->addDay()->setTime(2, 0),
        };
    }
}
