<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupOrchestratorService;
use Illuminate\Console\Command;

class BackupRunCommand extends Command
{
    protected $signature = 'backup:run {--type=full : full|db|files|pre_deploy|manual} {--origin=scheduler : scheduler|manual|deploy}';
    protected $description = 'Run a backup (database + files), encrypt, store, verify. Never marks success before verification.';

    public function handle(BackupOrchestratorService $orchestrator): int
    {
        $actor = auth()->user();
        try {
            $backup = $orchestrator->run($this->option('type'), $this->option('origin'), $actor);
            $this->info("Backup {$backup->backup_id} verified ({$backup->size_bytes} bytes).");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Backup failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
