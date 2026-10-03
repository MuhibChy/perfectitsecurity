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
        $phoneState = $this->verificationService->phoneStateFor($user);
        $phoneCountries = \App\Support\PhoneCountries::all();

        return view('customer.verification.phone', compact('user', 'phoneState', 'phoneCountries'));
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'size:2'],
            'national_number' => ['nullable', 'string', 'max:20'],
        ]);

        // Preferred: [country + national number] via the global registry.
        // Legacy: a full international number in `phone` still works.
        if ($request->filled('national_number')) {
            $country = strtoupper((string) $request->input('country', ''));
            if ($country === '' && $request->filled('country_search')) {
                $country = \App\Support\PhoneCountries::resolveInput((string) $request->input('country_search'))['alpha2'] ?? '';
            }
            abort_unless(\App\Support\PhoneCountries::isSupported($country), 422, 'Please select a valid country.');
            $phone = $this->verificationService->normalizeForCountry($request->input('national_number'), $country);
        } else {
            $request->validate(['phone' => ['required', 'string', 'max:20']]);
            $phone = (string) $request->input('phone');
        }

        $user = auth()->user();
        $result = $this->verificationService->start($user, $phone);

        $msg = $result['message'];
        if (app()->environment('local', 'testing') && ! empty($result['code'])) {
            $msg .= ' (Dev code: '.$result['code'].')';
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
