<?php

namespace App\Services\Health;

use App\Models\SystemErrorLog;

class ErrorLoggingService
{
    /**
     * Log or increment an error event.
     */
    public function logError(
        string $module,
        string $errorType,
        string $message,
        ?string $route = null,
        ?string $file = null,
        ?int $line = null,
        ?string $trace = null
    ): SystemErrorLog {
        // Sanitize message to strip sensitive credentials
        $sanitizedMessage = $this->sanitize($message);

        $existing = SystemErrorLog::unresolved()
            ->where('module', $module)
            ->where('error_type', $errorType)
            ->where('message', $sanitizedMessage)
            ->first();

        if ($existing) {
            $existing->increment('occurrences');
            $existing->update([
                'last_seen_at' => now(),
                'route' => $route ?? $existing->route,
            ]);
            return $existing;
        }

        return SystemErrorLog::create([
            'module' => $module,
            'route' => $route,
            'error_type' => $errorType,
            'message' => $sanitizedMessage,
            'file' => $file,
            'line' => $line,
            'trace' => $trace ? substr($trace, 0, 5000) : null,
            'status' => 'unresolved',
            'occurrences' => 1,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);
    }

    /**
     * Mark error resolved.
     */
    public function markResolved(int $id, ?string $note = null): bool
    {
        $log = SystemErrorLog::findOrFail($id);
        return $log->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'notes' => $note ? ($log->notes ? $log->notes . "\n" . $note : $note) : $log->notes,
        ]);
    }

    /**
     * Add note to error.
     */
    public function addNote(int $id, string $note): bool
    {
        $log = SystemErrorLog::findOrFail($id);
        $existingNotes = $log->notes ? $log->notes . "\n" : '';
        return $log->update([
            'notes' => $existingNotes . '[' . now()->toDateTimeString() . '] ' . $note,
        ]);
    }

    /**
     * Sanitize input strings to remove passwords, tokens, API keys.
     */
    protected function sanitize(string $text): string
    {
        $patterns = [
            '/(password|secret|token|apiKey|api_key|app_key|bearer\s+[a-zA-Z0-9_\-\.]+)=([^\s&]+)/i' => '$1=***REDACTED***',
            '/(Bearer\s+)[a-zA-Z0-9_\-\.]+/i' => '$1***REDACTED***',
        ];
        return preg_replace(array_keys($patterns), array_values($patterns), $text);
    }
}
