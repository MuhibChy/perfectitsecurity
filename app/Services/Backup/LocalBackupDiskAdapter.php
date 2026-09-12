<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Primary adapter: private `local` disk under storage/app/backups.
 * This directory is never exposed via the public/storage symlink.
 */
class LocalBackupDiskAdapter implements BackupStorageAdapter
{
    public function name(): string
    {
        return 'local';
    }

    private function disk()
    {
        return Storage::disk(config('backup.disk', 'local'));
    }

    public function put(string $localPath, string $destination): string
    {
        $base = trim(config('backup.directory', 'backups'), '/');
        $key = $base . '/' . ltrim($destination, '/');
        $contents = @file_get_contents($localPath);
        if ($contents === false) {
            throw new RuntimeException("Cannot read local file for backup upload: {$localPath}");
        }
        if (! $this->disk()->put($key, $contents)) {
            throw new RuntimeException("Failed to persist backup artifact: {$key}");
        }

        return $key;
    }

    public function get(string $identifier, string $localPath): void
    {
        $contents = $this->disk()->get($identifier);
        if ($contents === null) {
            throw new RuntimeException("Backup artifact not found in storage: {$identifier}");
        }
        if (@file_put_contents($localPath, $contents) === false) {
            throw new RuntimeException("Cannot stage backup artifact locally: {$localPath}");
        }
    }

    public function exists(string $identifier): bool
    {
        return $this->disk()->exists($identifier);
    }

    public function size(string $identifier): int
    {
        return (int) $this->disk()->size($identifier);
    }

    public function delete(string $identifier): void
    {
        $this->disk()->delete($identifier);
    }

    public function connectionTest(): void
    {
        $probe = trim(config('backup.directory', 'backups'), '/') . '/.connection-probe';
        if (! $this->disk()->put($probe, (string) time())) {
            throw new RuntimeException('Local backup disk is not writable.');
        }
        $this->disk()->delete($probe);
    }
}
