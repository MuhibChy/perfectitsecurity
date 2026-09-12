<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * External S3-compatible adapter. Engaged only when BACKUP_S3_ENABLED=true
 * with credentials present. Uses the app's `s3` filesystem disk so the
 * provider/region can change via env without code changes.
 */
class S3BackupAdapter implements BackupStorageAdapter
{
    public function name(): string
    {
        return 's3';
    }

    public static function isConfigured(): bool
    {
        return (bool) config('backup.s3.enabled')
            && ! empty(config('filesystems.disks.s3.bucket'));
    }

    private function disk()
    {
        return Storage::disk('s3');
    }

    private function key(string $destination): string
    {
        return trim((string) config('backup.s3.prefix', 'techsupport-backups'), '/') . '/' . ltrim($destination, '/');
    }

    public function put(string $localPath, string $destination): string
    {
        $stream = @fopen($localPath, 'rb');
        if (! $stream) {
            throw new RuntimeException("Cannot read local file for S3 upload: {$localPath}");
        }
        try {
            if (! $this->disk()->put($this->key($destination), $stream)) {
                throw new RuntimeException('S3 upload reported failure.');
            }
        } finally {
            @fclose($stream);
        }

        return 's3://' . $this->key($destination);
    }

    public function get(string $identifier, string $localPath): void
    {
        $key = preg_replace('#^s3://#', '', $identifier);
        $contents = $this->disk()->get($key);
        if ($contents === null) {
            throw new RuntimeException('Backup artifact not found in S3 storage.');
        }
        if (@file_put_contents($localPath, $contents) === false) {
            throw new RuntimeException("Cannot stage S3 artifact locally: {$localPath}");
        }
    }

    public function exists(string $identifier): bool
    {
        return $this->disk()->exists(preg_replace('#^s3://#', '', $identifier));
    }

    public function size(string $identifier): int
    {
        return (int) $this->disk()->size(preg_replace('#^s3://#', '', $identifier));
    }

    public function delete(string $identifier): void
    {
        $this->disk()->delete(preg_replace('#^s3://#', '', $identifier));
    }

    public function connectionTest(): void
    {
        if (! static::isConfigured()) {
            throw new RuntimeException('S3 backup storage is not configured (BACKUP_S3_ENABLED/bucket missing).');
        }
        $probe = $this->key('.connection-probe');
        if (! $this->disk()->put($probe, (string) time())) {
            throw new RuntimeException('S3 backup storage is not writable — check credentials/bucket/policy.');
        }
        $this->disk()->delete($probe);
    }
}
