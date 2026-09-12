<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch the application locale.
     */
    public function switch(Request $request, string $locale)
    {
        if (!array_key_exists($locale, SetLocale::SUPPORTED_LOCALES)) {
            abort(400, 'Unsupported locale.');
        }

        Session::put('locale', $locale);

        // Redirect back to the previous page or home
        $back = $request->header('referer', route('home'));
        return redirect($back);
    }
}
