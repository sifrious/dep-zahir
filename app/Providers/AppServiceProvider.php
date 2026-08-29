<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Provider integrations are deliberately bound outside the Zahir domain.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('zahir', function (Request $request): Limit {
            $credential = $request->bearerToken();

            return Limit::perMinute((int) config('zahir.requests_per_minute', 120))
                ->by(hash('sha256', is_string($credential) ? $credential : $request->ip()));
        });
    }
}
