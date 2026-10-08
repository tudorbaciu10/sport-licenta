<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [\App\Http\Middleware\SetLocale::class]);
        // Written by the cookie banner in the browser (consent) or holding just "ro"/"ru": not encrypted.
        $middleware->encryptCookies(except: [\App\Support\Consent::COOKIE, \App\Support\Consent::LOCALE_COOKIE]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
