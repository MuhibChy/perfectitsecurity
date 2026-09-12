<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Services\Backup\RestoreService;
use Illuminate\Console\Command;

class BackupRestoreCommand extends Command
{
    protected $signature = 'backup:restore {backup_id : backup_id to restore} {--scope=full : db|files|full} {--confirm-token= : must equal backup_id} {--test : isolated restore test only}';
    protected $description = 'Restore a verified backup (requires confirmation token + pre-restore safety backup).';

    public function handle(RestoreService $restore): int
    {
        $backup = Backup::where('backup_id', $this->argument('backup_id'))->firstOrFail();
        try {
            if ($this->option('test')) {
                $result = $restore->restoreTest($backup);
                $this->info('Restore test: ' . $result['status']);

                return self::SUCCESS;
            }
            $token = $this->option('confirm-token');
            if (! $token && ! $this->confirm("Restore backup {$backup->backup_id} (scope: {$this->option('scope')})? A pre-restore safety backup will be taken first.")) {
                $this->warn('Restore aborted.');

                return self::SUCCESS;
            }
            $result = $restore->restore($backup, $this->option('scope'), $token ?: $backup->backup_id, auth()->user());
            $this->info("Restore complete (safety backup: {$result['detail']['safety_backup_id']}).");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Restore failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
