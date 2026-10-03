<?php

namespace App\Http\Controllers\Concerns;

use App\Support\PhoneCountries;
use Illuminate\Http\Request;

/**
 * Resolves the shared phone-input component into canonical E.164.
 * Accepts [country alpha-2 + national number] (preferred) or a full
 * international number in `phone` (legacy). Returns null when no number
 * was supplied. The verification service stays the single normalizer.
 */
trait ResolvesPhoneInput
{
    protected function resolvePhoneInput(Request $request): ?string
    {
        if ($request->filled('national_number')) {
            $country = strtoupper(trim((string) $request->input('country', '')));
            if ($country === '' && $request->filled('country_search')) {
                $resolved = PhoneCountries::resolveInput((string) $request->input('country_search'));
                $country = $resolved['alpha2'] ?? '';
            }
            abort_unless(PhoneCountries::isSupported($country), 422, 'Please select a valid country.');

            return app(\App\Services\PhoneVerificationService::class)
                ->normalizeForCountry((string) $request->input('national_number'), $country);
        }
        $phone = trim((string) $request->input('phone', ''));

        return $phone === '' ? null : $phone;
    }
}
