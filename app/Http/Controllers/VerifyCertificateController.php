<?php

namespace App\Http\Controllers;

use App\Models\TrainingCertificate;

/**
 * Public certificate verification (signed token, minimal disclosure).
 * Revoked/expired certificates verify as NOT VALID, never valid.
 */
class VerifyCertificateController extends Controller
{
    public static function tokenFor(TrainingCertificate $certificate): string
    {
        return $certificate->id . '.' . hash_hmac('sha256', (string) $certificate->id, config('app.key'));
    }

    public function show(string $token)
    {
        $parts = explode('.', $token, 2);
        $certificate = null;
        if (count($parts) === 2 && ctype_digit($parts[0])
            && hash_equals(hash_hmac('sha256', $parts[0], config('app.key')), $parts[1])) {
            $certificate = TrainingCertificate::with(['user', 'course'])->find((int) $parts[0]);
        }
        $valid = $certificate && $certificate->status === 'active' && $certificate->user && ($certificate->user->is_active ?? true);
        return view('verify.certificate', compact('certificate', 'valid'));
    }
}
