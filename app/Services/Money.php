<?php

namespace App\Services;

use App\Models\Country;

/**
 * Central money formatting + precision. Every financial view must format
 * through here so decimal places, symbols, and code prefixes always follow
 * the currency catalog — never hard-coded assumptions.
 *
 * Storage note: money columns are decimal(12,2); display precision beyond
 * 2dp (KWD/BHD/OMR/JOD = 3) is presentation-level. Sub-fils precision is
 * rounded at storage — see docs.
 */
class Money
{
    /** @var array<string, array> catalog cache per request */
    protected static array $catalog = [];

    protected static function catalog(): array
    {
        if (empty(self::$catalog)) {
            foreach (Country::get(['currency_code', 'currency_symbol', 'currency_name', 'decimal_places', 'is_active']) as $c) {
                $code = strtoupper($c->currency_code);
                // First active row wins for shared codes (e.g. EUR).
                if (!isset(self::$catalog[$code]) || ($c->is_active && !self::$catalog[$code]['active'])) {
                    self::$catalog[$code] = [
                        'symbol' => $c->currency_symbol,
                        'name' => $c->currency_name,
                        'decimals' => (int) ($c->decimal_places ?? 2),
                        'active' => (bool) $c->is_active,
                    ];
                }
            }
        }
        return self::$catalog;
    }

    public static function flush(): void
    {
        self::$catalog = [];
    }

    public static function decimals(string $currency): int
    {
        return self::catalog()[strtoupper($currency)]['decimals'] ?? 2;
    }

    public static function symbol(string $currency): string
    {
        return self::catalog()[strtoupper($currency)]['symbol'] ?? (strtoupper($currency) . ' ');
    }

    public static function isSupported(string $currency): bool
    {
        return isset(self::catalog()[strtoupper($currency)]);
    }

    public static function isActive(string $currency): bool
    {
        return (bool) (self::catalog()[strtoupper($currency)]['active'] ?? false);
    }

    public static function round(float $amount, string $currency): float
    {
        return round($amount, self::decimals($currency));
    }

    /**
     * "AED 1,500.00" / "KWD 500.000" — symbol + grouped decimals.
     */
    public static function format(float $amount, string $currency): string
    {
        $code = strtoupper($currency);
        return self::symbol($code) . number_format($amount, self::decimals($code));
    }

    /**
     * Code-prefixed variant for PDF/ASCII-safe contexts.
     * "AED 1,500.00".
     */
    public static function formatCode(float $amount, string $currency): string
    {
        $code = strtoupper($currency);
        return $code . ' ' . number_format($amount, self::decimals($code));
    }
}
