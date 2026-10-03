<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function switch(Request $request, string $currency, CurrencyService $fx)
    {
        $currency = strtoupper($currency);
        // Single source of truth: active catalog rows. No hard-coded list.
        if (!\App\Services\Money::isActive($currency)) {
            abort(400, 'Unsupported currency.');
        }

        // Customers are limited to their own allowed set (local + USD);
        // staff and guests keep the global catalog (storefront browsing).
        $user = $request->user();
        if ($user && $user->isCustomer()
            && !app(\App\Services\CustomerCurrencyService::class)->allows($user, $currency)) {
            abort(422, 'This currency is not available for your account.');
        }

        $fx->setSessionCurrency($currency);
        if ($request->user()) {
            $request->user()->forceFill(['preferred_currency' => $currency])->save();
        }

        return back();
    }
}
