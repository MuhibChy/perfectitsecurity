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
        if (! array_key_exists($locale, SetLocale::SUPPORTED_LOCALES)) {
            abort(400, 'Unsupported locale.');
        }

        Session::put('locale', $locale);

        // Redirect back to the previous page or home. The Referer header is
        // attacker-spoofable, so only relative paths or same-host absolute
        // URLs are honored — anything else falls back to home (no open redirect).
        $back = (string) $request->header('referer', route('home'));
        // NOTE: `~` delimiter is used because the pattern itself matches
        // literal `#` (fragment) characters — with a `#` delimiter the
        // unescaped `#` inside `[?#]` would terminate the pattern early
        // and preg_match would throw ("Unknown modifier ']'"), turning
        // every /lang/* switch into a 500.
        if (! preg_match('~^/([^\s?#]*)?([?#][^\s]*)?$~', $back)) {
            $host = parse_url($back, PHP_URL_HOST);
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);
            if (! $host || ! $appHost || strtolower($host) !== strtolower($appHost)) {
                return redirect()->route('home');
            }
        }

        return redirect($back);
    }
}
