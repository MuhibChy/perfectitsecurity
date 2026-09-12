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
        $allowed = collect($fx->supportedCurrencies())->pluck('currency_code')->map(fn ($c) => strtoupper($c))->all();
        if (!in_array($currency, $allowed, true) && !in_array($currency, ['USD', 'GBP', 'EUR', 'BDT'], true)) {
            abort(400, 'Unsupported currency.');
        }

        $fx->setSessionCurrency($currency);
        if ($request->user()) {
            $request->user()->forceFill(['preferred_currency' => $currency])->save();
        }

        return back();
    }
}
