<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Services\Backup\BackupVerificationService;
use App\Services\Backup\LocalBackupDiskAdapter;
use Illuminate\Console\Command;

class BackupVerifyCommand extends Command
{
    protected $signature = 'backup:verify {backup_id : backup_id to re-verify}';
    protected $description = 'Re-verify a stored backup (checksum, archive, upload, metadata).';

    public function handle(): int
    {
        $backup = Backup::where('backup_id', $this->argument('backup_id'))->firstOrFail();
        $workDir = storage_path('app/backups/tmp/verify-' . uniqid('', true));
        @mkdir($workDir, 0755, true);
        try {
            $detail = (new BackupVerificationService())->verify($backup, new LocalBackupDiskAdapter(), $workDir);
            $backup->update([
                'verification_status' => 'passed', 'verified_at' => now(),
                'status' => 'verified', 'verification_detail' => json_encode($detail),
            ]);
            $this->info("Backup {$backup->backup_id} verification passed.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $backup->update(['verification_status' => 'failed', 'verified_at' => now(), 'error_message' => substr($e->getMessage(), 0, 2000)]);
            $this->error('Verification failed: ' . $e->getMessage());

            return self::FAILURE;
        } finally {
            foreach (glob($workDir . DIRECTORY_SEPARATOR . '*') ?: [] as $f) {
                @unlink($f);
            }
            @rmdir($workDir);
        }
    }
}
