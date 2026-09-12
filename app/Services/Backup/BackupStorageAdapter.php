<?php

namespace App\Services\Backup;

/**
 * Storage abstraction so the provider can change without rewriting backup logic.
 * Paths are internal identifiers — never public URLs.
 */
interface BackupStorageAdapter
{
    public function name(): string;

    /** Persist a local file to this storage; returns the internal identifier. */
    public function put(string $localPath, string $destination): string;

    /** Retrieve a stored artifact to a local path for verify/restore. */
    public function get(string $identifier, string $localPath): void;

    public function exists(string $identifier): bool;

    public function size(string $identifier): int;

    public function delete(string $identifier): void;

    /** Lightweight connectivity/permission probe; throws on failure. */
    public function connectionTest(): void;
}
