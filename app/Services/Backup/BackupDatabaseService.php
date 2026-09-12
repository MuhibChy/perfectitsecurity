<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Consistent database backups for both supported engines.
 * - mysql: native mysqldump --single-transaction (consistent InnoDB snapshot).
 * - sqlite: WAL checkpoint + atomic file copy (safe while app is running).
 * Output is a gzipped SQL (mysql) or gzipped sqlite file. Never loads the
 * whole DB into PHP memory as text.
 */
class BackupDatabaseService
{
    /** @return array{path: string, bytes: int, checksum: string, db_version: string} */
    public function dump(string $workDir): array
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'mysql' => $this->dumpMysql($workDir),
            'sqlite' => $this->dumpSqlite($workDir),
            default => throw new RuntimeException("Unsupported database driver for backups: {$driver}"),
        };
    }

    private function dumpMysql(string $workDir): array
    {
        $binary = $this->findMysqldump();
        $cfg = config('database.connections.mysql');
        $out = $workDir . DIRECTORY_SEPARATOR . 'database.sql.gz';

        $cmd = sprintf(
            '%s --single-transaction --quick --routines --triggers --events --set-gtid-purged=OFF --default-character-set=utf8mb4 -h %s -P %s -u %s %s',
            escapeshellarg($binary),
            escapeshellarg($cfg['host'] ?? '127.0.0.1'),
            escapeshellarg($cfg['port'] ?? '3306'),
            escapeshellarg($cfg['username'] ?? ''),
            escapeshellarg($cfg['database'] ?? '')
        );

        $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $env = ['MYSQL_PWD' => (string) ($cfg['password'] ?? '')] + getenv();
        $proc = proc_open($cmd, $descriptors, $pipes, null, $env);
        if (! is_resource($proc)) {
            throw new RuntimeException('Could not start mysqldump process.');
        }
        fclose($pipes[0]);
        $gz = gzopen($out, 'wb9');
        if (! $gz) {
            proc_close($proc);
            throw new RuntimeException('Cannot create database backup staging file.');
        }
        while (! feof($pipes[1])) {
            $chunk = fread($pipes[1], 8192);
            if ($chunk !== false && $chunk !== '') {
                gzwrite($gz, $chunk);
            }
        }
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exit = proc_close($proc);
        gzclose($gz);

        if ($exit !== 0) {
            @unlink($out);
            throw new RuntimeException('mysqldump failed: ' . trim((string) $stderr));
        }

        $version = '';
        try {
            $version = (string) DB::selectOne('select version() as v')->v;
        } catch (\Throwable) {
        }

        return $this->describe($out, 'MySQL ' . $version);
    }

    private function dumpSqlite(string $workDir): array
    {
        $source = DB::connection()->getDatabaseName();
        if (! is_file($source)) {
            // Covers :memory: (tests) and any non-file DSN: portable SQL dump.
            return $this->dumpSqliteLogical($workDir);
        }
        // Checkpoint WAL so the copy is consistent, then atomic copy.
        try {
            DB::statement('PRAGMA wal_checkpoint(TRUNCATE)');
        } catch (\Throwable) {
        }
        $copy = $workDir . DIRECTORY_SEPARATOR . 'database.sqlite';
        if (! @copy($source, $copy)) {
            throw new RuntimeException('SQLite backup copy failed.');
        }
        // Also copy WAL/SHM sidecars when present for completeness.
        foreach (['-wal', '-shm', '-journal'] as $suffix) {
            if (is_file($source . $suffix)) {
                @copy($source . $suffix, $copy . $suffix);
            }
        }
        $out = $workDir . DIRECTORY_SEPARATOR . 'database.sqlite.gz';
        $gz = gzopen($out, 'wb9');
        $in = fopen($copy, 'rb');
        if (! $gz || ! $in) {
            throw new RuntimeException('Cannot stage SQLite backup archive.');
        }
        while (! feof($in)) {
            $chunk = fread($in, 8192);
            if ($chunk !== false && $chunk !== '') {
                gzwrite($gz, $chunk);
            }
        }
        fclose($in);
        gzclose($gz);
        @unlink($copy);
        foreach (['-wal', '-shm', '-journal'] as $suffix) {
            @unlink($copy . $suffix);
        }

        try {
            $sqliteVersion = (string) DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\Throwable) {
            $sqliteVersion = 'unknown';
        }

        return $this->describe($out, 'SQLite ' . $sqliteVersion);
    }

    /**
     * Portable logical SQL dump for non-file SQLite (e.g. :memory: in tests).
     * Streams CREATE + INSERT statements into gzip; values are PDO-quoted.
     */
    private function dumpSqliteLogical(string $workDir): array
    {
        $pdo = DB::connection()->getPdo();
        $out = $workDir . DIRECTORY_SEPARATOR . 'database.sql.gz';
        $gz = gzopen($out, 'wb9');
        if (! $gz) {
            throw new RuntimeException('Cannot stage SQLite backup archive.');
        }
        gzwrite($gz, "-- TechSupport sqlite logical backup " . now()->toIso8601String() . "\nPRAGMA foreign_keys=OFF;\n");
        $tables = $pdo->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($tables as $t) {
            gzwrite($gz, "\n" . $t['sql'] . ";\n");
        }
        foreach ($tables as $t) {
            $name = str_replace('"', '""', $t['name']);
            $rows = $pdo->query("SELECT * FROM \"{$name}\"")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $vals = array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), array_values($row));
                gzwrite($gz, "INSERT INTO \"{$name}\" VALUES (" . implode(',', $vals) . ");\n");
            }
        }
        gzwrite($gz, "PRAGMA foreign_keys=ON;\n");
        gzclose($gz);
        try {
            $sqliteVersion = (string) $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\Throwable) {
            $sqliteVersion = 'unknown';
        }

        return $this->describe($out, 'SQLite ' . $sqliteVersion . ' (logical)');
    }

    /** Validate a staged DB archive without touching the live database. */
    public function validateArchive(string $path): string
    {
        if (! is_file($path) || filesize($path) === 0) {
            throw new RuntimeException('Database backup archive is empty or missing.');
        }
        // gzip integrity check (streaming, bounded memory).
        $gz = gzopen($path, 'rb');
        if (! $gz) {
            throw new RuntimeException('Database backup archive is not valid gzip.');
        }
        $bytes = 0;
        $head = '';
        while (! gzeof($gz)) {
            $chunk = gzread($gz, 8192);
            if ($chunk === false) {
                gzclose($gz);
                throw new RuntimeException('Database backup archive failed integrity check.');
            }
            if ($bytes < 4096) {
                $head .= substr($chunk, 0, 4096 - $bytes);
            }
            $bytes += strlen($chunk);
        }
        gzclose($gz);
        if ($bytes === 0) {
            throw new RuntimeException('Database backup archive decompresses to zero bytes.');
        }

        return $head;
    }

    private function findMysqldump(): string
    {
        foreach (['mysqldump', '/usr/bin/mysqldump', '/usr/local/bin/mysqldump'] as $candidate) {
            $check = stripos(PHP_OS, 'WIN') === 0
                ? "where {$candidate} 2>NUL"
                : "command -v {$candidate} 2>/dev/null";
            $found = @shell_exec($check);
            if (is_string($found) && trim($found) !== '') {
                return $candidate;
            }
        }

        throw new RuntimeException('mysqldump binary not found — install MySQL client tools on the backup host.');
    }

    private function describe(string $path, string $dbVersion): array
    {
        return [
            'path' => $path,
            'bytes' => (int) filesize($path),
            'checksum' => hash_file('sha256', $path),
            'db_version' => trim($dbVersion),
        ];
    }
}
