<?php

namespace App\Services;

use App\Models\Country;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * CustomerCurrencyService — per-customer currency availability.
 *
 * GLOBAL RULE: USD is the universal default/fallback.
 * Every customer receives AT MOST two currencies:
 *   1. their location/country-based currency (when supported), plus
 *   2. USD as the universal fallback.
 *
 * Resolution priority (customer's own account data is authoritative —
 * never blind browser IP geolocation for a permanent account currency):
 *   1. explicit verified country_code on the user record,
 *   2. account country/address text matched to the country registry,
 *   3. safe fallback → USD only.
 *
 * Unsupported country → [USD]. US customer → [USD] (never [USD, USD]).
 *
 * Changing a preference never mutates historical records: invoices,
 * payments and ledger rows keep their stored authoritative currency.
 */
class CustomerCurrencyService
{
    public const FALLBACK = 'USD';

    /** ISO country-code overrides for free-text country values. */
    protected const COUNTRY_ALIASES = [
        'UK' => 'UK', 'GB' => 'UK', 'GREAT BRITAIN' => 'UK', 'ENGLAND' => 'UK',
        'USA' => 'US', 'UNITED STATES OF AMERICA' => 'US',
    ];

    /** Resolve the registry country code for a user (null when unknown). */
    public function countryCodeFor(User $user): ?string
    {
        $explicit = strtoupper(trim((string) ($user->country_code ?? '')));
        if ($explicit !== '' && $this->activeCountry($explicit)) {
            return $this->activeCountry($explicit)->code;
        }

        return $this->resolveCountryCode((string) ($user->country ?? ''));
    }

    /** Match free-text country input to a registry code (null when unknown). */
    public function resolveCountryCode(string $text): ?string
    {
        $text = strtoupper(trim($text));
        if ($text === '') {
            return null;
        }
        if (isset(self::COUNTRY_ALIASES[$text])) {
            $text = self::COUNTRY_ALIASES[$text];
        }
        // Exact code or name match wins (active rows preferred).
        $match = Country::whereRaw('UPPER(code) = ?', [$text])->first()
            ?? Country::whereRaw('UPPER(name) = ?', [$text])->first();
        if ($match) {
            return $match->code;
        }
        // Lenient contains-match for values like "United Kingdom (London)".
        $match = Country::where('is_active', true)
            ->where(function ($q) use ($text) {
                $q->whereRaw('UPPER(name) LIKE ?', ['%'.$text.'%'])
                    ->orWhereRaw('? LIKE \'%\' || UPPER(name) || \'%\'', [$text]);
            })->orderBy('sort_order')->first();

        return $match?->code;
    }

    /** Supported local currency code for a user (null → USD-only fallback). */
    public function localCurrencyFor(User $user): ?string
    {
        $code = $this->countryCodeFor($user);
        if (! $code) {
            return null;
        }
        $country = $this->activeCountry($code);
        if (! $country) {
            return null;
        }
        $ccy = strtoupper($country->currency_code);

        return Money::isActive($ccy) ? $ccy : null;
    }

    /**
     * Currencies the customer may use, in order: [local?, USD].
     * Cached per account; invalidated automatically when the user record
     * (country/preference) changes.
     *
     * @return string[]
     */
    public function availableFor(User $user): array
    {
        $key = 'cust_ccy:'.$user->id.':'.($user->updated_at?->timestamp ?? 0)
            .':'.md5((string) ($user->country_code ?? '').'|'.(string) ($user->country ?? ''));

        return Cache::remember($key, 600, function () use ($user) {
            $fresh = $user->fresh() ?? $user;
            $local = $this->localCurrencyFor($fresh);
            if (! $local || $local === self::FALLBACK) {
                return [self::FALLBACK];
            }

            return [$local, self::FALLBACK];
        });
    }

    /** Whether $code is an allowed currency choice for this customer. */
    public function allows(User $user, string $code): bool
    {
        return in_array(strtoupper($code), $this->availableFor($user), true);
    }

    /** Display metadata for the customer's allowed currencies. */
    public function detailsFor(User $user): array
    {
        $out = [];
        foreach ($this->availableFor($user) as $code) {
            $country = Country::active()->where('currency_code', $code)->orderBy('sort_order')->first();
            $out[] = [
                'currency_code' => $code,
                'currency_name' => $country->currency_name ?? $code,
                'currency_symbol' => $country->currency_symbol ?? $code,
            ];
        }

        return $out;
    }

    protected function activeCountry(string $code): ?Country
    {
        return Country::where('code', $code)->where('is_active', true)->first();
    }
}
