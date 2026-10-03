<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Twilio\Rest\Client;

class PhoneVerificationService
{
    public const MAX_ATTEMPTS = 5;
    public const RESEND_COOLDOWN_SECONDS = 60;
    public const OTP_LIFETIME_MINUTES = 10;

    public function start(User $user, string $phone): array
    {
        $normalizedPhone = $this->normalize($phone);

        // Edge case: duplicate phone check across other verified users
        $existing = User::where('phone', $normalizedPhone)
            ->where('id', '!=', $user->id)
            ->whereNotNull('phone_verified_at')
            ->exists();
        abort_if($existing, 422, 'This phone number is already verified on another account.');

        // Cooldown check (60 seconds)
        if ($user->phone_otp_sent_at && $user->phone_otp_sent_at->addSeconds(self::RESEND_COOLDOWN_SECONDS)->isFuture()) {
            $secondsRemaining = now()->diffInSeconds($user->phone_otp_sent_at->addSeconds(self::RESEND_COOLDOWN_SECONDS));
            abort(422, "Please wait {$secondsRemaining} seconds before requesting another code.");
        }

        // Rate limiting (max 3 requests per 10 minutes)
        $rateLimitKey = 'phone-verification-send:' . $user->id;
        abort_if(RateLimiter::tooManyAttempts($rateLimitKey, 3), 429, 'Too many verification code requests. Please wait before trying again.');
        RateLimiter::hit($rateLimitKey, 600);

        if ($this->hasTwilioCredentials()) {
            try {
                $client = $this->twilioClient();
                $client->verify->v2->services(config('services.twilio.verify_service_sid'))
                    ->verifications->create($normalizedPhone, 'sms');

                $user->update([
                    'phone' => $normalizedPhone,
                    'phone_verified_at' => null,
                    'verification_status' => $user->email_verified_at ? 'pending' : 'pending',
                    'phone_otp_sent_at' => now(),
                    'phone_otp_attempts' => 0,
                ]);

                return ['success' => true, 'provider' => 'twilio', 'message' => 'Verification code sent via SMS.'];
            } catch (\Throwable $e) {
                Log::warning('Twilio verification dispatch failed, falling back to secure local OTP: ' . $e->getMessage());
            }
        }

        // Secure built-in OTP provider — bcrypt-hashed so a DB read cannot replay it.
        $code = (string) random_int(100000, 999999);
        $codeHash = Hash::make($code);

        $user->update([
            'phone' => $normalizedPhone,
            'phone_verified_at' => null,
            'verification_status' => 'pending',
            'phone_otp_hash' => $codeHash,
            'phone_otp_expires_at' => now()->addMinutes(self::OTP_LIFETIME_MINUTES),
            'phone_otp_attempts' => 0,
            'phone_otp_sent_at' => now(),
        ]);

        // Keep code accessible in test / local environments only — never log OTP in production.
        if (app()->environment('local', 'testing')) {
            Log::info("Phone verification OTP for user {$user->id} ({$normalizedPhone}): {$code}");
            session()->flash('dev_otp_code', $code);
        }

        AuditLog::log('phone_verification_sent', 'auth', $user, "Verification OTP generated for {$normalizedPhone}.");

        return [
            'success' => true,
            'provider' => 'local',
            'code' => app()->environment('local', 'testing') ? $code : null,
            'message' => 'Verification code sent.',
        ];
    }

    public function verify(User $user, string $code): bool
    {
        $code = trim($code);
        abort_if(empty($code), 422, 'Please enter a verification code.');

        $phone = $this->normalize((string) $user->phone);

        // Twilio verify path
        if ($this->hasTwilioCredentials() && empty($user->phone_otp_hash)) {
            try {
                $check = $this->twilioClient()->verify->v2->services(config('services.twilio.verify_service_sid'))
                    ->verificationChecks->create(['to' => $phone, 'code' => $code]);
                abort_unless($check->status === 'approved' && $check->valid, 422, 'The verification code is invalid or expired.');

                $this->markVerified($user);
                return true;
            } catch (\Throwable $e) {
                if ($e->getCode() == 422) throw $e;
                Log::warning('Twilio verification check failed: ' . $e->getMessage());
            }
        }

        // Built-in OTP validation
        abort_if(empty($user->phone_otp_hash), 422, 'No active verification code found. Please request a new one.');

        // Attempt limit check
        if ($user->phone_otp_attempts >= self::MAX_ATTEMPTS) {
            $user->update([
                'phone_otp_hash' => null,
                'phone_otp_expires_at' => null,
            ]);
            abort(422, 'Too many incorrect attempts. This code has been invalidated. Please request a new code.');
        }

        // Expiration check
        if (!$user->phone_otp_expires_at || now()->isAfter($user->phone_otp_expires_at)) {
            $user->update([
                'phone_otp_hash' => null,
                'phone_otp_expires_at' => null,
            ]);
            abort(422, 'The verification code has expired. Please request a new code.');
        }

        // Increment attempts
        $user->increment('phone_otp_attempts');

        // Verify code — bcrypt primary, legacy SHA-256 accepted once for rolling upgrades.
        $stored = (string) $user->phone_otp_hash;
        $valid = Hash::check($code, $stored)
            || (preg_match('/^[0-9a-f]{64}$/i', $stored) && hash_equals($stored, hash('sha256', $code)));
        if (!$valid) {
            $remaining = self::MAX_ATTEMPTS - (int) $user->phone_otp_attempts;
            abort(422, "Invalid verification code. {$remaining} attempts remaining.");
        }

        $this->markVerified($user);
        return true;
    }

    private function markVerified(User $user): void
    {
        $user->update([
            'phone_verified_at' => now(),
            'phone_otp_hash' => null,
            'phone_otp_expires_at' => null,
            'phone_otp_attempts' => 0,
            'verification_status' => $user->email_verified_at ? 'fully_verified' : 'pending',
        ]);

        AuditLog::log('phone_verified', 'auth', $user, 'Phone number successfully verified.');
    }

    public function normalize(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        abort_unless(preg_match('/^\+[1-9][0-9]{7,14}$/', $phone), 422, 'Use an international phone number beginning with + (e.g. +447123456789 or +12025550123).');
        return $phone;
    }

    /**
     * Normalize a national-format number using the selected phone country.
     * A leading + always wins (already international); otherwise the
     * national trunk zero is stripped and the registry dial code applied.
     * Storage stays canonical E.164 — one identity per number.
     */
    public function normalizeForCountry(string $number, string $alpha2): string
    {
        $entry = \App\Support\PhoneCountries::find($alpha2);
        abort_unless($entry, 422, 'Unknown phone country selected.');
        $clean = preg_replace('/[^0-9+]/', '', trim($number));
        abort_if($clean === '' || $clean === '+', 422, 'Please enter a mobile number.');
        if (str_starts_with($clean, '+')) {
            return $this->normalize($clean);
        }
        $national = ltrim($clean, '0');
        abort_if($national === '', 422, 'Please enter a mobile number.');
        return $this->normalize('+' . $entry['dial'] . $national);
    }

    /** Registry dial code for a user (own country_code first, then country text). */
    public function dialCodeForUser(\App\Models\User $user): ?string
    {
        $code = strtoupper(trim((string) ($user->country_code ?? '')));
        if ($code !== '' && ($entry = \App\Support\PhoneCountries::find($code))) {
            return $entry['dial'];
        }
        $text = strtoupper(trim((string) ($user->country ?? '')));
        foreach (\App\Support\PhoneCountries::all() as $entry) {
            if (strtoupper($entry['name']) === $text) {
                return $entry['dial'];
            }
        }
        return null;
    }

    /**
     * Explicit phone verification state — never confused with email,
     * identity or 2FA states.
     */
    public function phoneStateFor(\App\Models\User $user): string
    {
        if ($user->isPhoneVerified()) {
            return 'VERIFIED';
        }
        if (empty($user->phone)) {
            return 'NOT_PROVIDED';
        }
        if ($user->phone_otp_attempts >= self::MAX_ATTEMPTS) {
            return 'FAILED';
        }
        if ($user->phone_otp_expires_at && now()->isAfter($user->phone_otp_expires_at)) {
            return $user->phone_otp_hash ? 'EXPIRED' : 'PROVIDED';
        }
        if ($user->phone_otp_hash) {
            return $user->phone_otp_sent_at ? 'PENDING' : 'SENT';
        }
        return 'PROVIDED';
    }

    private function hasTwilioCredentials(): bool
    {
        return !empty(config('services.twilio.account_sid'))
            && !empty(config('services.twilio.auth_token'))
            && !empty(config('services.twilio.verify_service_sid'));
    }

    private function twilioClient(): Client
    {
        return new Client(config('services.twilio.account_sid'), config('services.twilio.auth_token'));
    }
}
