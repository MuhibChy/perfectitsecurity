<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class EmailVerificationService
{
    public const MAX_ATTEMPTS = 5;
    public const RESEND_COOLDOWN_SECONDS = 60;
    public const OTP_LIFETIME_MINUTES = 10;

    /**
     * Send an email OTP to the given user.
     */
    public function send(User $user): array
    {
        // Cooldown check
        if ($user->email_otp_sent_at && $user->email_otp_sent_at->addSeconds(self::RESEND_COOLDOWN_SECONDS)->isFuture()) {
            $seconds = now()->diffInSeconds($user->email_otp_sent_at->addSeconds(self::RESEND_COOLDOWN_SECONDS));
            abort(422, "Please wait {$seconds} seconds before requesting another code.");
        }

        // Rate limiting: max 3 per 10 minutes
        $key = 'email-verification:' . $user->id;
        abort_if(RateLimiter::tooManyAttempts($key, 3), 429, 'Too many verification requests. Please wait.');
        RateLimiter::hit($key, 600);

        // Generate secure OTP
        $code = (string) random_int(100000, 999999);
        $hash = hash('sha256', $code);

        $user->update([
            'email_otp_hash' => $hash,
            'email_otp_expires_at' => now()->addMinutes(self::OTP_LIFETIME_MINUTES),
            'email_otp_attempts' => 0,
            'email_otp_sent_at' => now(),
        ]);

        // Send email (simplified – assume mailable exists)
        try {
            \Mail::to($user->email)->send(new \App\Mail\EmailOtpMail($code));
        } catch (\Throwable $e) {
            Log::warning('Failed to send email OTP: ' . $e->getMessage());
        }

        if (app()->environment('local', 'testing')) {
            Log::info("Email OTP for user {$user->id}: {$code}");
            session()->flash('dev_email_otp', $code);
        }

        return [
            'success' => true,
            'provider' => 'email',
            'code' => app()->environment('local', 'testing') ? $code : null,
            'message' => 'Verification code sent to email.',
        ];
    }

    /**
     * Verify the submitted OTP.
     */
    public function verify(User $user, string $code): bool
    {
        $code = trim($code);
        abort_if(empty($code), 422, 'Please enter a verification code.');

        abort_if(empty($user->email_otp_hash), 422, 'No active verification code. Request a new one.');

        // Attempt limit
        if ($user->email_otp_attempts >= self::MAX_ATTEMPTS) {
            $user->update([
                'email_otp_hash' => null,
                'email_otp_expires_at' => null,
                'email_otp_attempts' => 0,
            ]);
            abort(422, 'Too many incorrect attempts. Code invalidated.');
        }

        // Expiration
        if (!$user->email_otp_expires_at || now()->isAfter($user->email_otp_expires_at)) {
            $user->update([
                'email_otp_hash' => null,
                'email_otp_expires_at' => null,
            ]);
            abort(422, 'Verification code expired.');
        }

        $user->increment('email_otp_attempts');
        $inputHash = hash('sha256', $code);
        if (!hash_equals($user->email_otp_hash, $inputHash)) {
            $remaining = self::MAX_ATTEMPTS - $user->email_otp_attempts;
            abort(422, "Invalid code. {$remaining} attempts remaining.");
        }

        // Mark email verified
        $user->update([
            'email_verified_at' => now(),
            'email_otp_hash' => null,
            'email_otp_expires_at' => null,
            'email_otp_attempts' => 0,
        ]);

        return true;
    }
}
