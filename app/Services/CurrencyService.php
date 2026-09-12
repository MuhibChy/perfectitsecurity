<?php

namespace App\Services;

use App\Models\Country;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    public const DEFAULT_BASE = 'USD';

    public function supportedCurrencies(): array
    {
        return Country::active()
            ->orderBy('sort_order')
            ->get(['currency_code', 'currency_symbol', 'currency_name', 'code', 'name'])
            ->unique('currency_code')
            ->values()
            ->all();
    }

    public function getRate(string $from, string $to): float
    {
        $from = strtoupper($from);
        $to = strtoupper($to);
        if ($from === $to) {
            return 1.0;
        }

        $direct = ExchangeRate::where('base_currency', $from)->where('target_currency', $to)->first();
        if ($direct) {
            return (float) $direct->rate;
        }

        $inverse = ExchangeRate::where('base_currency', $to)->where('target_currency', $from)->first();
        if ($inverse && (float) $inverse->rate > 0) {
            return 1 / (float) $inverse->rate;
        }

        // Cross via USD
        $fromUsd = ExchangeRate::where('base_currency', self::DEFAULT_BASE)->where('target_currency', $from)->first();
        $toUsd = ExchangeRate::where('base_currency', self::DEFAULT_BASE)->where('target_currency', $to)->first();
        if ($fromUsd && $toUsd && (float) $fromUsd->rate > 0) {
            return (float) $toUsd->rate / (float) $fromUsd->rate;
        }

        return 1.0;
    }

    public function convert(float $amount, string $from, string $to): float
    {
        return round($amount * $this->getRate($from, $to), 2);
    }

    public function format(float $amount, string $currency): string
    {
        $country = Country::where('currency_code', strtoupper($currency))->first();
        $symbol = $country?->currency_symbol ?? strtoupper($currency) . ' ';
        return $symbol . number_format($amount, 2);
    }

    public function setSessionCurrency(string $currency): void
    {
        session(['currency' => strtoupper($currency)]);
    }

    public function currentCurrency(): string
    {
        return strtoupper(session('currency', auth()->user()?->preferred_currency ?? self::DEFAULT_BASE));
    }

    /**
     * Seed or refresh static rates (no live API required). Optional Frankfurter fetch when network allowed.
     */
    public function refreshRates(bool $tryRemote = false): void
    {
        $defaults = [
            'GBP' => 0.79,
            'BDT' => 110.0,
            'EUR' => 0.92,
            'USD' => 1.0,
        ];

        if ($tryRemote) {
            try {
                $response = Http::timeout(5)->get('https://api.frankfurter.app/latest', ['from' => 'USD']);
                if ($response->successful()) {
                    $rates = $response->json('rates') ?? [];
                    foreach ($rates as $code => $rate) {
                        $defaults[$code] = (float) $rate;
                    }
                }
            } catch (\Throwable $e) {
                Log::info('FX remote refresh skipped: ' . $e->getMessage());
            }
        }

        foreach ($defaults as $code => $rate) {
            ExchangeRate::updateOrCreate(
                ['base_currency' => 'USD', 'target_currency' => $code],
                ['rate' => $rate, 'fetched_at' => now()]
            );
        }

        Cache::forget('fx_rates');
    }
}
