<?php

namespace App\Compliance;

final readonly class StripeWebsiteReadiness
{
    public function missingRequirements(): array
    {
        $requirements = [
            'Business name' => config('business.name'),
            'Customer support email' => config('business.support_email'),
            'A second customer support method' => config('business.support_phone') ?: config('business.address'),
            'Logres displayed price' => config('business.products.logres.price'),
            'Logres purchase currency' => config('business.products.logres.currency'),
            'Stripe secret key' => config('services.stripe.secret'),
            'Stripe webhook signing secret' => config('services.stripe.webhook_secret'),
            'Stripe Logres Price ID' => config('services.stripe.prices.logres.price_id'),
        ];

        foreach (['privacy', 'terms', 'refunds', 'cancellations', 'delivery'] as $document) {
            $requirements[config("trust.documents.{$document}.title")] = config("trust.documents.{$document}.published");
        }

        return collect($requirements)
            ->reject(fn (mixed $value) => $this->isPresent($value))
            ->keys()
            ->all();
    }

    public function isReady(): bool
    {
        return $this->missingRequirements() === [];
    }

    private function isPresent(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filled($value);
    }
}
