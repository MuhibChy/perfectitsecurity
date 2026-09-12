<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Supported languages with their native names.
     */
    public const SUPPORTED_LOCALES = [
        'en' => ['name' => 'English', 'native' => 'English', 'dir' => 'ltr'],
        'bn' => ['name' => 'Bengali', 'native' => 'বাংলা', 'dir' => 'ltr'],
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'dir' => 'rtl'],
    ];

    /**
     * Default locale.
     */
    public const DEFAULT_LOCALE = 'en';

    public function handle(Request $request, Closure $next)
    {
        // 1. Check query parameter (?lang=bn)
        if ($queryLocale = $request->query('lang')) {
            if (array_key_exists($queryLocale, self::SUPPORTED_LOCALES)) {
                Session::put('locale', $queryLocale);
            }
        }

        // 2. Check session
        $locale = Session::get('locale');

        // 3. Check browser Accept-Language header
        if (!$locale) {
            $browserLocale = $request->getPreferredLanguage(array_keys(self::SUPPORTED_LOCALES));
            $locale = $browserLocale ?: self::DEFAULT_LOCALE;
        }

        // Set the application locale
        App::setLocale($locale);

        // Share locale data with all views
        \View::share('currentLocale', $locale);
        \View::share('supportedLocales', self::SUPPORTED_LOCALES);

        return $next($request);
    }
}
