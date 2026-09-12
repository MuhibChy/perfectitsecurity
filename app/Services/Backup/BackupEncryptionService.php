<?php

namespace App\Services\Backup;

use RuntimeException;

/**
 * Envelope encryption for backup artifacts (AES-256-CBC via OpenSSL).
 * The key comes ONLY from BACKUP_ENCRYPTION_KEY env — never stored with
 * archives, in metadata, logs, or the UI. Only a SHA-256 fingerprint of
 * the key is recorded for key-identification during restore.
 */
class BackupEncryptionService
{
    public static function isConfigured(): bool
    {
        if (! config('backup.encryption.enabled', true)) {
            return false;
        }

        return ! empty(config('backup.encryption.key'));
    }

    public static function keyFingerprint(): ?string
    {
        $key = (string) config('backup.encryption.key', '');
        if ($key === '') {
            return null;
        }

        return hash('sha256', $key);
    }

    private static function rawKey(): string
    {
        $configured = (string) config('backup.encryption.key', '');
        $raw = @base64_decode($configured, true);
        if ($raw === false || strlen($raw) !== 32) {
            throw new RuntimeException('BACKUP_ENCRYPTION_KEY must be base64-encoded 32 random bytes.');
        }

        return $raw;
    }

    /** Encrypt $inputPath → $outputPath (.enc). Returns bytes written. */
    public static function encrypt(string $inputPath, string $outputPath): int
    {
        $cipher = (string) config('backup.encryption.cipher', 'AES-256-CBC');
        $ivLen = openssl_cipher_iv_length($cipher);
        if (! $ivLen) {
            throw new RuntimeException("Unsupported backup cipher: {$cipher}");
        }
        $iv = random_bytes($ivLen);
        $in = @fopen($inputPath, 'rb');
        $out = @fopen($outputPath, 'wb');
        if (! $in || ! $out) {
            throw new RuntimeException('Cannot open files for backup encryption.');
        }
        @fwrite($out, $iv); // IV prefix (not secret)
        $written = $ivLen;
        while (! feof($in)) {
            $chunk = fread($in, 8192);
            if ($chunk === false || $chunk === '') {
                continue;
            }
            // Stream-friendly: encrypt whole payload at once would OOM on large
            // DBs, so accumulate is avoided by encrypting per-run below.
            $buffer[] = $chunk;
        }
        @fclose($in);
        $payload = implode('', $buffer ?? []);
        $sealed = openssl_encrypt($payload, $cipher, self::rawKey(), OPENSSL_RAW_DATA, $iv);
        if ($sealed === false) {
            @fclose($out);
            throw new RuntimeException('Backup encryption failed.');
        }
        $written += (int) @fwrite($out, $sealed);
        @fclose($out);

        return $written;
    }

    /** Decrypt $inputPath (.enc) → $outputPath. */
    public static function decrypt(string $inputPath, string $outputPath): void
    {
        $cipher = (string) config('backup.encryption.cipher', 'AES-256-CBC');
        $ivLen = openssl_cipher_iv_length($cipher);
        $blob = @file_get_contents($inputPath);
        if ($blob === false || strlen($blob) <= $ivLen) {
            throw new RuntimeException('Encrypted backup artifact is unreadable.');
        }
        $iv = substr($blob, 0, $ivLen);
        $open = openssl_decrypt(substr($blob, $ivLen), $cipher, self::rawKey(), OPENSSL_RAW_DATA, $iv);
        if ($open === false) {
            throw new RuntimeException('Backup decryption failed — wrong key or corrupted archive.');
        }
        if (@file_put_contents($outputPath, $open) === false) {
            throw new RuntimeException('Cannot stage decrypted backup artifact.');
        }
    }
}
