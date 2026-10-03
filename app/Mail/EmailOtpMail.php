<?php

namespace App\Mail;

use App\Services\EmailVerificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code)
    {
    }

    public function build()
    {
        return $this->subject('Your Email Verification Code')
            ->view('emails.email_otp')
            ->with([
                'otp' => $this->code,
                'expiration' => now()->addMinutes(EmailVerificationService::OTP_LIFETIME_MINUTES)->toDayDateTimeString(),
            ]);
    }
}
