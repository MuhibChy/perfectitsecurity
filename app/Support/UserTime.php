<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Per-user timezone rendering. All timestamps are STORED in UTC
 * (config/app.php timezone = UTC — never change storage). This helper only
 * converts for DISPLAY, falling back to UTC when the user has no (or an
 * invalid) timezone. PHP's timezone database handles DST transitions.
 */
class UserTime
{
    public const FALLBACK = 'UTC';

    public static function for(?object $user): string
    {
        $tz = $user->timezone ?? null;
        if (is_string($tz) && $tz !== '') {
            try {
                new \DateTimeZone($tz);

                return $tz;
            } catch (\Throwable $e) {
                // Fall through to UTC.
            }
        }

        return config('app.display_timezone', self::FALLBACK);
    }

    public static function format(mixed $dt, ?object $user, string $format = 'Y-m-d H:i'): string
    {
        if (empty($dt)) {
            return '—';
        }
        $tz = self::for($user);
        try {
            return Carbon::parse($dt)->tz($tz)->format($format);
        } catch (\Throwable $e) {
            return Carbon::parse($dt)->tz(self::FALLBACK)->format($format);
        }
    }
}
