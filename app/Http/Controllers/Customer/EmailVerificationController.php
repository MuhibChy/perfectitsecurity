<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    protected EmailVerificationService $service;

    public function __construct(EmailVerificationService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the email verification OTP entry page.
     */
    public function show()
    {
        return view('customer.email.verify');
    }

    /**
     * Send a new OTP to the user's email.
     */
    public function sendOtp(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $result = $this->service->send($user);
        return back()->with('status', $result['message'] ?? 'OTP sent');
    }

    /**
     * Verify the submitted OTP.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();
        $this->service->verify($user, $request->input('code'));
        return redirect()->route('portal.dashboard')->with('success', 'Email address successfully verified.');
    }
}
