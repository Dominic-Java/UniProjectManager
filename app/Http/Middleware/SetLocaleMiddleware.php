<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    private const SUPPORTED = ['ro', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $userLocale = strtolower(trim((string) $request->user()?->locale_preference));
        if (in_array($userLocale, self::SUPPORTED, true)) {
            return $userLocale;
        }

        $cookieLocale = strtolower(trim((string) $request->cookie('upm_locale', '')));
        if (in_array($cookieLocale, self::SUPPORTED, true)) {
            return $cookieLocale;
        }

        $defaultLocale = strtolower(trim((string) config('app.locale', 'ro')));
        if (in_array($defaultLocale, self::SUPPORTED, true)) {
            return $defaultLocale;
        }

        return 'ro';
    }
}
