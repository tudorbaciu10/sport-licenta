<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported interface languages.
     */
    public const SUPPORTED = ['ro', 'en', 'ru'];

    /**
     * Apply the locale stored in the session (default: Romanian).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', 'ro');

        if (in_array($locale, self::SUPPORTED, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
