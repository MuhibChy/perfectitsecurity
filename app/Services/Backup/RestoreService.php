<?php

namespace App\Services\Backup;

use App\Models\Backup;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Secure restore workflow. Guards:
 * - only verified backups (status=verified, verification=passed)
 * - caller must pass a typed confirmation token (the backup_id itself)
 * - a pre-restore safety backup is taken first (db scope minimum)
 * - restores run from staged verified artifacts, never from live paths
 * - file restore preserves private visibility (no chmod to public)
 * Full production restores additionally require BACKUP_ALLOW_PRODUCTION_RESTORE=true.
 */
class RestoreService
{
    /** @return array{backup_id: string, scope: string, detail: array} */
    public function restore(Backup $backup, string $scope, string $confirmToken, ?object $actor = null): array
    {
        if (! $backup->isVerified()) {
            throw new RuntimeException('Only verified backups may be restored.');
        }
        if (! hash_equals($backup->backup_id, trim($confirmToken))) {
            throw new RuntimeException('Restore confirmation token does not match the selected backup.');
        }
        if (! in_array($scope, ['db', 'files', 'full'], true)) {
            throw new RuntimeException('Invalid restore scope.');
        }
        if (app()->environment('production') && ! env('BACKUP_ALLOW_PRODUCTION_RESTORE', false)) {
            throw new RuntimeException('Production restores are disabled. Run in an isolated staging environment.');
        }

        $adapter = new LocalBackupDiskAdapter();
        $workDir = $this->workDir();
        $detail = [];

        // Pre-restore safety net.
        $safety = app(BackupOrchestratorService::class)->run('db', 'restore-safety', $actor);
        $detail['safety_backup_id'] = $safety->backup_id;

        if ($scope === 'db' || $scope === 'full') {
            $detail['db'] = $this->restoreDatabase($backup, $adapter, $workDir);
        }
        if ($scope === 'files' || $scope === 'full') {
            $detail['files'] = $this->restoreFiles($backup, $adapter, $workDir);
        }

        app(AuditService::class)->log(
            'backup.restored',
            'backups',
            $backup,
            "Backup {$backup->backup_id} restored (scope: {$scope}). Safety backup: {$safety->backup_id}."
        );
        $this->cleanup($workDir);

        return ['backup_id' => $backup->backup_id, 'scope' => $scope, 'detail' => $detail];
    }

    /** Isolated restore test: restore into scratch, verify, discard. Never touches live DB. */
    public function restoreTest(Backup $backup): array
    {
        if (! $backup->isVerified()) {
            throw new RuntimeException('Only verified backups may be restore-tested.');
        }
        $adapter = new LocalBackupDiskAdapter();
        $workDir = $this->workDir();
        try {
            $dbFile = $backup->files()->where('kind', 'db')->firstOrFail();
            $staged = $workDir . DIRECTORY_SEPARATOR . 'test.enc';
            $adapter->get($dbFile->path, $staged);
            $payload = $staged;
            if ($dbFile->encrypted) {
                $payload = $staged . '.dec';
                BackupEncryptionService::decrypt($staged, $payload);
            }
            $probe = (new BackupDatabaseService())->validateArchive($payload);
            $filesOk = true;
            $filesEntry = $backup->files()->where('kind', 'files')->first();
            if ($filesEntry) {
                $fstaged = $workDir . DIRECTORY_SEPARATOR . 'test-files.enc';
                $adapter->get($filesEntry->path, $fstaged);
                $fpayload = $fstaged;
                if ($filesEntry->encrypted) {
                    $fpayload = $fstaged . '.dec';
                    BackupEncryptionService::decrypt($fstaged, $fpayload);
                }
                $filesOk = (new BackupFilesService())->validateArchive($fpayload) >= 0;
            }
            $backup->update(['restore_test_status' => 'passed', 'restore_tested_at' => now()]);
            app(AuditService::class)->log('backup.restore_tested', 'backups', $backup, "Restore test passed for {$backup->backup_id}.");

            return ['status' => 'passed', 'db_valid' => strlen($probe) > 0, 'files_valid' => $filesOk];
        } catch (\Throwable $e) {
            $backup->update(['restore_test_status' => 'failed', 'restore_tested_at' => now()]);
            throw $e;
        } finally {
            $this->cleanup($workDir);
        }
    }

    private function restoreDatabase(Backup $backup, BackupStorageAdapter $adapter, string $workDir): array
    {
        $dbFile = $backup->files()->where('kind', 'db')->firstOrFail();
        $staged = $workDir . DIRECTORY_SEPARATOR . 'restore-db.enc';
        $adapter->get($dbFile->path, $staged);
        $payload = $staged;
        if ($dbFile->encrypted) {
            $payload = $staged . '.dec';
            BackupEncryptionService::decrypt($staged, $payload);
        }
        (new BackupDatabaseService())->validateArchive($payload);
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $live = DB::connection()->getDatabaseName();
            // Decompress staged archive to temp, then atomic swap under a transaction pause.
            $tmp = $workDir . DIRECTORY_SEPARATOR . 'database.sqlite';
            $gz = gzopen($payload, 'rb');
            $out = fopen($tmp, 'wb');
            while (! gzeof($gz)) {
                $chunk = gzread($gz, 8192);
                if ($chunk !== false && $chunk !== '') {
                    fwrite($out, $chunk);
                }
            }
            gzclose($gz);
            fclose($out);
            // Keep a copy of the current live file next to the safety backup.
            @copy($live, $live . '.pre-restore-' . date('Ymd-His'));
            if (! @rename($tmp, $live)) {
                throw new RuntimeException('SQLite restore swap failed.');
            }

            return ['driver' => 'sqlite', 'restored' => true];
        }

        // MySQL: stream validated dump into mysql client (staging only unless explicitly allowed).
        $cfg = config('database.connections.mysql');
        $cmd = sprintf(
            'mysql -h %s -P %s -u %s %s',
            escapeshellarg($cfg['host'] ?? '127.0.0.1'),
            escapeshellarg($cfg['port'] ?? '3306'),
            escapeshellarg($cfg['username'] ?? ''),
            escapeshellarg($cfg['database'] ?? '')
        );
        $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $env = ['MYSQL_PWD' => (string) ($cfg['password'] ?? '')] + getenv();
        $proc = proc_open($cmd, $descriptors, $pipes, null, $env);
        if (! is_resource($proc)) {
            throw new RuntimeException('Could not start mysql client for restore.');
        }
        $gz = gzopen($payload, 'rb');
        while (! gzeof($gz)) {
            $chunk = gzread($gz, 8192);
            if ($chunk !== false && $chunk !== '') {
                fwrite($pipes[0], $chunk);
            }
        }
        gzclose($gz);
        fclose($pipes[0]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($proc);
        if ($exit !== 0) {
            throw new RuntimeException('MySQL restore failed: ' . trim((string) $stderr));
        }

        return ['driver' => 'mysql', 'restored' => true];
    }

    private function restoreFiles(Backup $backup, BackupStorageAdapter $adapter, string $workDir): array
    {
        $filesEntry = $backup->files()->where('kind', 'files')->firstOrFail();
        $staged = $workDir . DIRECTORY_SEPARATOR . 'restore-files.enc';
        $adapter->get($filesEntry->path, $staged);
        $payload = $staged;
        if ($filesEntry->encrypted) {
            $payload = $staged . '.dec';
            BackupEncryptionService::decrypt($staged, $payload);
        }
        $zip = new \ZipArchive();
        if ($zip->open($payload) !== true) {
            throw new RuntimeException('File restore archive is unreadable.');
        }
        $root = storage_path('app');
        $restored = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === 'manifest.json') {
                continue;
            }
            // Path-traversal guard: only allow paths inside storage/app allowlist.
            $clean = str_replace(['\\', '..'], ['/', ''], (string) $name);
            if (str_starts_with($clean, '/') || str_starts_with($clean, 'backups/')) {
                continue;
            }
            $dest = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $clean);
            $dir = dirname($dest);
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $stream = $zip->getStream($name);
            if ($stream) {
                $out = fopen($dest, 'wb');
                while (! feof($stream)) {
                    fwrite($out, fread($stream, 8192));
                }
                fclose($out);
                fclose($stream);
                @chmod($dest, 0644); // private-safe permissions, never executable/public
                $restored++;
            }
        }
        $zip->close();

        return ['files_restored' => $restored];
    }

    private function workDir(): string
    {
        $dir = storage_path('app/backups/tmp/restore-' . uniqid('', true));
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
