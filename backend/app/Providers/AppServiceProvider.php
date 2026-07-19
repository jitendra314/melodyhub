<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('resend-otp', function (Request $request) {

            return Limit::perMinutes(
                config('auth.otp_decay_minutes'),
                config('auth.otp_max_attempts')
            )->by(
                strtolower($request->input('email', $request->ip()))
            );
        });
    }
}
