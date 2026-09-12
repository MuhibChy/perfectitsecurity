<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupRetentionService;
use Illuminate\Console\Command;

class BackupPruneCommand extends Command
{
    protected $signature = 'backup:prune';
    protected $description = 'Apply retention policy (never deletes sole backup, in-restore, or held backups).';

    public function handle(BackupRetentionService $retention): int
    {
        $result = $retention->prune(auth()->user());
        $this->info('Retention cleanup: ' . count($result['deleted']) . ' deleted, ' . $result['kept'] . ' kept.');

        return self::SUCCESS;
    }
}
