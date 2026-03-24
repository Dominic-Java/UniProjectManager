<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): array {
            $email = strtolower(trim((string) $request->input('email', 'guest')));
            if ($email === '') {
                $email = 'guest';
            }

            return [
                Limit::perMinute(6)->by($email . '|' . $request->ip()),
                Limit::perMinute(20)->by('login-ip|' . $request->ip()),
            ];
        });

        RateLimiter::for('password-reset-request', function (Request $request): array {
            $email = strtolower(trim((string) $request->input('email', 'guest')));
            if ($email === '') {
                $email = 'guest';
            }

            return [
                Limit::perMinute(4)->by($email . '|' . $request->ip()),
                Limit::perHour(20)->by('reset-request-ip|' . $request->ip()),
            ];
        });

        RateLimiter::for('password-reset-submit', function (Request $request): array {
            $email = strtolower(trim((string) $request->input('email', 'guest')));
            if ($email === '') {
                $email = 'guest';
            }

            return [
                Limit::perMinute(5)->by($email . '|' . $request->ip()),
                Limit::perHour(30)->by('reset-submit-ip|' . $request->ip()),
            ];
        });

        if (config('uniprojectmanager.force_https')) {
            URL::forceScheme('https');
        }
    }
}
