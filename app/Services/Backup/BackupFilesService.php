<?php

namespace App\Services\Backup;

use RuntimeException;

/**
 * Zipped backup of allowlisted storage/app subdirectories.
 * Preserves relative paths (privacy preserved on restore), records a
 * per-file SHA-256 manifest, skips temp/partial files, tolerates missing
 * sources (logged in manifest rather than failing the run).
 */
class BackupFilesService
{
    /** @return array{path: string, bytes: int, checksum: string, file_count: int, manifest: array} */
    public function archive(string $workDir): array
    {
        if (! class_exists(\ZipArchive::class)) {
            throw new RuntimeException('PHP zip extension is required for file backups.');
        }
        $root = storage_path('app');
        $out = $workDir . DIRECTORY_SEPARATOR . 'files.zip';
        $zip = new \ZipArchive();
        if ($zip->open($out, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Cannot create file backup archive.');
        }

        $manifest = [];
        $count = 0;
        foreach ((array) config('backup.file_sources', []) as $source) {
            $abs = $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $source);
            if (! is_dir($abs)) {
                $manifest[] = ['path' => $source, 'status' => 'missing_skipped'];
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($abs, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($iterator as $file) {
                /** @var \SplFileInfo $file */
                if (! $file->isFile()) {
                    continue;
                }
                $name = $file->getFilename();
                // Skip temp/partial uploads and safety checkpoints.
                if (preg_match('/\.(tmp|part|lock)$/i', $name)) {
                    continue;
                }
                $relative = substr($file->getPathname(), strlen($root) + 1);
                if (str_starts_with($relative, 'backups' . DIRECTORY_SEPARATOR)
                    || str_starts_with($relative, 'backups/')) {
                    continue; // never back up previous backups into themselves
                }
                if (! $zip->addFile($file->getPathname(), $relative)) {
                    continue;
                }
                $manifest[] = [
                    'path' => $relative,
                    'bytes' => $file->getSize(),
                    'sha256' => @hash_file('sha256', $file->getPathname()) ?: null,
                    'status' => 'ok',
                ];
                $count++;
            }
        }
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        $zip->close();

        if (! is_file($out) || filesize($out) === 0) {
            throw new RuntimeException('File backup archive is empty.');
        }

        return [
            'path' => $out,
            'bytes' => (int) filesize($out),
            'checksum' => hash_file('sha256', $out),
            'file_count' => $count,
            'manifest' => $manifest,
        ];
    }

    /** Reopen + verify a staged files archive; returns manifest entry count. */
    public function validateArchive(string $path): int
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File backup archive failed integrity check (unreadable zip).');
        }
        $manifestIdx = $zip->locateName('manifest.json');
        if ($manifestIdx === false) {
            $zip->close();
            throw new RuntimeException('File backup archive is missing its manifest.');
        }
        $manifest = json_decode((string) $zip->getFromIndex($manifestIdx), true);
        $zip->close();
        if (! is_array($manifest)) {
            throw new RuntimeException('File backup manifest is corrupted.');
        }

        return count($manifest);
    }
}
