<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\PhoneVerificationService;
use Illuminate\Http\Request;

class PhoneVerificationController extends Controller
{
    public function __construct(private PhoneVerificationService $verificationService)
    {
    }

    public function show()
    {
        $user = auth()->user();
        return view('customer.verification.phone', compact('user'));
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $user = auth()->user();
        $result = $this->verificationService->start($user, $request->input('phone'));

        $msg = $result['message'];
        if (app()->environment('local', 'testing') && !empty($result['code'])) {
            $msg .= ' (Dev code: ' . $result['code'] . ')';
        }

        return back()->with('success', $msg);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'min:4', 'max:10'],
        ]);

        $user = auth()->user();
        $this->verificationService->verify($user, $request->input('code'));

        return redirect()->route('portal.verification.phone')->with('success', 'Phone number successfully verified!');
    }
}
