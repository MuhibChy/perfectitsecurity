<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\PhoneVerificationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * SyntheticTestCustomerSeeder — exactly 10 clearly-marked synthetic
 * customer accounts covering the supported/unsupported country matrix.
 *
 * SAFETY:
 *  - Refuses to run in production (staging/test mechanism only).
 *  - Test-domain emails only; is_demo=true; updateOrCreate (no duplicates).
 *  - Verification follows the legitimate workflow: the same
 *    markEmailAsVerified() the signed verification route calls, plus the
 *    real PhoneVerificationService OTP start/verify cycle (local provider
 *    in non-production). Every step is audit-logged.
 *  - Currencies use ONLY the application's country registry (unsupported
 *    scenarios intentionally resolve to the USD fallback).
 */
class SyntheticTestCustomerSeeder extends Seeder
{
    public const DOMAIN = 'example.test';

    public const PREFIX = 'TEST-CUSTOMER-';

    /** tag, country text, ISO code (null = unsupported), phone, expected currencies */
    public const MATRIX = [
        ['01', 'United Kingdom', 'UK', '+447700900101', ['GBP', 'USD']],
        ['02', 'United States', 'US', '+12025550102', ['USD']],
        ['03', 'Bangladesh', 'BD', '+8801700000103', ['BDT', 'USD']],
        ['04', 'Germany', 'DE', '+491700000104', ['EUR', 'USD']],
        ['05', 'United Arab Emirates', 'AE', '+971500000105', ['AED', 'USD']],
        ['06', 'Saudi Arabia', 'SA', '+966500000106', ['SAR', 'USD']],
        ['07', 'Kuwait', 'KW', '+96550000107', ['KWD', 'USD']],
        ['08', 'Atlantis', null, '+819000001108', ['USD']],
        ['09', 'France', 'FR', '+33600000109', ['EUR', 'USD']],
        ['10', 'Qatar', 'QA', '+97450000110', ['QAR', 'USD']],
    ];

    public function run(): void
    {
        abort_if(app()->environment('production'), 403, 'Synthetic test accounts must never be seeded in production.');

        $password = env('TEST_SEED_PASSWORD');
        $otp = app(PhoneVerificationService::class);

        foreach (self::MATRIX as [$tag, $country, $code, $phone, $expected]) {
            $user = User::updateOrCreate(
                ['email' => 'test-customer-'.$tag.'@'.self::DOMAIN],
                [
                    'name' => self::PREFIX.$tag,
                    'role' => 'customer',
                    'is_active' => true,
                    'is_demo' => true,
                    'country' => $country,
                    'country_code' => $code,
                    'preferred_currency' => $expected[0],
                    'password' => $password ? Hash::make($password) : Hash::make(Str::random(32)),
                ]
            );

            // Legitimate email verification (same state change the signed
            // verification link performs via EmailVerificationRequest).
            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
                AuditLog::log('synthetic.email_verified', 'users', $user, 'Synthetic '.self::PREFIX.$tag.' email verified via standard workflow.');
            }

            // Legitimate phone verification through the real OTP service.
            if (! $user->isPhoneVerified()) {
                $started = $otp->start($user->fresh(), $phone);
                if (! empty($started['code'])) {
                    $otp->verify($user->fresh(), $started['code']);
                    AuditLog::log('synthetic.phone_verified', 'users', $user, 'Synthetic '.self::PREFIX.$tag.' phone verified via OTP workflow.');
                }
            }

            // Assert the currency contract immediately (fail loudly in seed).
            $actual = $user->fresh()->availableCurrencies();
            abort_unless($actual === $expected, 500, 'Currency contract broken for '.self::PREFIX.$tag.': '.json_encode($actual));
        }
    }
}
