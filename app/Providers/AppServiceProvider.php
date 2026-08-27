<?php

namespace App\Providers;

use App\Stripe\Contracts\StripeGateway;
use App\Stripe\StripeSdkGateway;
use App\Stripe\StripeWebhookVerifier;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StripeClient::class, fn () => new StripeClient(
            (string) config('services.stripe.secret'),
        ));

        $this->app->bind(StripeGateway::class, StripeSdkGateway::class);

        $this->app->singleton(StripeWebhookVerifier::class, fn () => new StripeWebhookVerifier(
            (string) config('services.stripe.webhook_secret'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
