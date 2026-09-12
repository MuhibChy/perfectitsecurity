<?php

namespace App\Services\Backup;

use App\Models\Backup;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Single entry point for backup runs.
 * Flow: guard → snapshot row → db/files artifacts → encrypt → upload
 * (local, then S3 when configured) → verify → mark verified → notify →
 * retention cleanup. A backup is NEVER marked successful before verification.
 * Duplicate simultaneous runs are prevented via cache lock.
 */
class BackupOrchestratorService
{
    public function run(string $type = 'full', string $origin = 'scheduler', ?object $actor = null): Backup
    {
        if (! config('backup.enabled', true)) {
            throw new RuntimeException('Backup system is disabled (BACKUP_ENABLED=false).');
        }
        if (! in_array($type, ['full', 'db', 'files', 'pre_deploy', 'manual'], true)) {
            throw new RuntimeException('Invalid backup type.');
        }
        $scope = $type === 'manual' ? 'full' : ($type === 'pre_deploy' ? 'full' : $type);

        $lock = cache()->lock('backup-run', 3600);
        if (! $lock->acquire()) {
            throw new RuntimeException('Another backup is already running — duplicate run prevented.');
        }

        $workDir = $this->workDir();
        $backup = Backup::create([
            'backup_id' => 'BKP-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(6)),
            'type' => $type,
            'scope' => $scope,
            'status' => 'running',
            'started_at' => now(),
            'origin' => $origin,
            'created_by' => $actor?->id,
            'app_version' => trim((string) @shell_exec('php artisan --version')) ?: config('app.name'),
            'storage' => S3BackupAdapter::isConfigured() ? 'local+s3' : 'local',
        ]);
        app(AuditService::class)->log('backup.started', 'backups', $backup, "Backup {$backup->backup_id} started (type: {$type}, origin: {$origin}).");
        $started = microtime(true);

        try {
            $artifacts = []; // kind => [path, bytes, checksum, ...]
            $plainChecksums = [];

            if ($scope === 'full' || $scope === 'db') {
                $db = (new BackupDatabaseService())->dump($workDir);
                $artifacts['db'] = $db;
                $plainChecksums['db'] = $db['checksum'];
                $backup->update(['db_version' => $db['db_version']]);
            }
            if ($scope === 'full' || $scope === 'files') {
                $files = (new BackupFilesService())->archive($workDir);
                $artifacts['files'] = $files;
                $plainChecksums['files'] = $files['checksum'];
            }

            $totalPlain = array_sum(array_map(fn ($a) => $a['bytes'], $artifacts));
            if ($totalPlain > (int) config('backup.max_bytes', 2 * 1024 * 1024 * 1024)) {
                throw new RuntimeException('Backup exceeds BACKUP_MAX_BYTES guard — refusing to store.');
            }

            // Encrypt envelopes (when a key is configured).
            $encrypted = BackupEncryptionService::isConfigured();
            if (config('backup.encryption.enabled', true) && ! $encrypted) {
                throw new RuntimeException('Backup encryption is enabled but BACKUP_ENCRYPTION_KEY is missing.');
            }
            $storeFiles = [];
            foreach ($artifacts as $kind => $info) {
                $payload = $info['path'];
                $isEnc = false;
                if ($encrypted) {
                    $encPath = $payload . '.enc';
                    BackupEncryptionService::encrypt($payload, $encPath);
                    $payload = $encPath;
                    $isEnc = true;
                }
                $storeFiles[$kind] = [
                    'payload' => $payload,
                    'bytes' => filesize($payload),
                    'checksum' => $isEnc ? hash_file('sha256', $payload) : $info['checksum'],
                    'extra' => $info,
                    'encrypted' => $isEnc,
                ];
            }

            // Upload: local first (authoritative), then S3 mirror when configured.
            $adapter = new LocalBackupDiskAdapter();
            $adapter->connectionTest();
            foreach ($storeFiles as $kind => $sf) {
                $destination = $backup->backup_id . '/' . $kind . ($sf['encrypted'] ? '.enc' : ($kind === 'db' ? '.gz' : '.zip'));
                $identifier = $adapter->put($sf['payload'], $destination);
                $backup->files()->create([
                    'kind' => $kind,
                    'disk' => 'local',
                    'path' => $identifier,
                    'size_bytes' => $sf['bytes'],
                    'checksum' => $sf['checksum'],
                    'encrypted' => $sf['encrypted'],
                    'file_count' => $sf['extra']['file_count'] ?? null,
                ]);
            }

            if (S3BackupAdapter::isConfigured()) {
                $s3 = new S3BackupAdapter();
                try {
                    foreach ($storeFiles as $kind => $sf) {
                        $destination = $backup->backup_id . '/' . $kind . ($sf['encrypted'] ? '.enc' : ($kind === 'db' ? '.gz' : '.zip'));
                        $s3->put($sf['payload'], $destination);
                    }
                } catch (\Throwable $e) {
                    throw new RuntimeException('External (S3) upload failed — backup NOT marked successful: ' . $e->getMessage());
                }
            }

            $backup->update([
                'size_bytes' => $backup->files()->sum('size_bytes'),
                'encrypted' => $encrypted,
                'encryption_cipher' => $encrypted ? config('backup.encryption.cipher') : null,
                'encryption_key_fingerprint' => $encrypted ? BackupEncryptionService::keyFingerprint() : null,
                'checksum' => hash('sha256', $backup->files()->orderBy('kind')->pluck('checksum')->implode('|')),
                'status' => 'verifying',
                'verification_detail' => json_encode(['plain_checksums' => $plainChecksums]),
            ]);

            // Integrity verification (throws on any failure).
            $verifyWork = $this->workDir();
            try {
                $detail = (new BackupVerificationService())->verify($backup->fresh('files'), $adapter, $verifyWork);
            } finally {
                $this->cleanup($verifyWork);
            }
            $backup->update([
                'status' => 'verified',
                'completed_at' => now(),
                'duration_ms' => (int) ((microtime(true) - $started) * 1000),
                'verification_status' => 'passed',
                'verified_at' => now(),
                'verification_detail' => json_encode($detail + ['plain_checksums' => $plainChecksums]),
            ]);

            app(AuditService::class)->log('backup.completed', 'backups', $backup, "Backup {$backup->backup_id} verified ({$backup->size_bytes} bytes).");
            $this->notify($backup, true);

            // Retention cleanup (best-effort; never fails the backup itself).
            try {
                app(BackupRetentionService::class)->prune($actor);
            } catch (\Throwable $e) {
                app(AuditService::class)->log('backup.retention_failed', 'backups', $backup, 'Retention cleanup failed: ' . $e->getMessage());
            }

            return $backup->fresh('files');
        } catch (\Throwable $e) {
            $backup->update([
                'status' => 'failed',
                'completed_at' => now(),
                'duration_ms' => (int) ((microtime(true) - $started) * 1000),
                'error_message' => substr($e->getMessage(), 0, 2000),
            ]);
            app(AuditService::class)->log('backup.failed', 'backups', $backup, "Backup {$backup->backup_id} failed: " . $e->getMessage());
            $this->notify($backup->fresh(), false, $e->getMessage());
            throw $e;
        } finally {
            $this->cleanup($workDir);
            $lock->release();
        }
    }

    public function storageStatus(): array
    {
        $local = ['ok' => true, 'detail' => 'writable'];
        try {
            (new LocalBackupDiskAdapter())->connectionTest();
        } catch (\Throwable $e) {
            $local = ['ok' => false, 'detail' => $e->getMessage()];
        }
        $s3 = ['configured' => S3BackupAdapter::isConfigured(), 'ok' => null, 'detail' => null];
        if ($s3['configured']) {
            try {
                (new S3BackupAdapter())->connectionTest();
                $s3['ok'] = true;
                $s3['detail'] = 'reachable';
            } catch (\Throwable $e) {
                $s3['ok'] = false;
                $s3['detail'] = $e->getMessage();
            }
        }

        return ['local' => $local, 's3' => $s3];
    }

    private function notify(Backup $backup, bool $success, ?string $error = null): void
    {
        $to = config('backup.notifications.mail_to');
        $want = $success ? config('backup.notifications.on_success', false) : config('backup.notifications.on_failure', true);
        if (! $to || ! $want) {
            return;
        }
        try {
            Mail::raw(
                $success
                    ? "Backup {$backup->backup_id} ({$backup->type}) verified successfully. Size: {$backup->size_bytes} bytes."
                    : "Backup {$backup->backup_id} ({$backup->type}) FAILED. Error: " . ($error ?: $backup->error_message),
                fn ($m) => $m->to($to)->subject($success ? "[Backup] {$backup->backup_id} verified" : "[Backup] {$backup->backup_id} FAILED")
            );
        } catch (\Throwable) {
        }
    }

    private function workDir(): string
    {
        $dir = storage_path('app/backups/tmp/run-' . uniqid('', true));
        @mkdir($dir, 0755, true);

        return $dir;
    }

    private function cleanup(string $dir): void
    {
        foreach (glob($dir . DIRECTORY_SEPARATOR . '*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($dir);
    }
}
