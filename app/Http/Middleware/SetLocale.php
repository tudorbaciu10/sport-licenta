<?php

namespace App\Http\Middleware;

use App\Support\Consent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the language the visitor picked: from the session, or — only if they accepted
 * preference cookies — from the long-lived "sportmd_locale" cookie. RO by default.
 */
class SetLocale
{
    public const SUPPORTED = ['ro', 'ru'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (! $locale && Consent::preferences($request)) {
            $locale = $request->cookie(Consent::LOCALE_COOKIE);
        }

        if (in_array($locale, self::SUPPORTED, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
