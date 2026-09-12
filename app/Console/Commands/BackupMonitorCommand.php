<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Services\Backup\BackupOrchestratorService;
use Illuminate\Console\Command;

class BackupMonitorCommand extends Command
{
    protected $signature = 'backup:monitor';
    protected $description = 'Check backup health: overdue, failed, unverified, storage unreachable.';

    public function handle(BackupOrchestratorService $orchestrator): int
    {
        $failures = 0;
        $last = Backup::active()->latest()->first();
        if (! $last) {
            $this->warn('No backups recorded yet.');
            $failures++;
        } else {
            if ($last->status === 'failed') {
                $this->error("Last backup {$last->backup_id} FAILED: {$last->error_message}");
                $failures++;
            }
            if ($last->created_at->lt(now()->subHours(30)) && $last->type === 'full') {
                $this->error("Last full backup is overdue ({$last->created_at->diffForHumans()}).");
                $failures++;
            }
            $unverified = Backup::active()->where('verification_status', '!=', 'passed')->where('status', '!=', 'failed')->count();
            if ($unverified > 0) {
                $this->error("{$unverified} backup(s) are not verified.");
                $failures++;
            }
        }
        if (! config('backup.encryption.enabled', true) || empty(config('backup.encryption.key'))) {
            $this->error('Backup encryption key is missing (BACKUP_ENCRYPTION_KEY).');
            $failures++;
        }
        $storage = $orchestrator->storageStatus();
        if (! $storage['local']['ok']) {
            $this->error('Local backup storage unavailable: ' . $storage['local']['detail']);
            $failures++;
        }
        if ($storage['s3']['configured'] && $storage['s3']['ok'] === false) {
            $this->error('S3 backup storage unreachable: ' . $storage['s3']['detail']);
            $failures++;
        }
        if ($failures === 0) {
            $this->info('Backup health OK.');
        }

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }
}
