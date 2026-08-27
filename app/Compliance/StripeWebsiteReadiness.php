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
            'Stripe secret key' => config('services.stripe.secret'),
            'Stripe webhook signing secret' => config('services.stripe.webhook_secret'),
            'Policy version' => config('business.policy_version'),
            'Policy effective date' => config('business.policy_effective_at'),
            'Initial purchase refund window' => config('business.initial_refund_days'),
            'Renewal refund review window' => config('business.renewal_refund_days'),
            'Cancellation effective timing' => config('business.cancellation_effective'),
            'Digital delivery timing' => config('business.delivery_timing'),
        ];

        foreach (config('products') as $product) {
            foreach ($product['plans'] as $plan) {
                $label = "{$product['name']} {$plan['name']}";
                $requirements["{$label} displayed price"] = $plan['price'];
                $requirements["{$label} purchase currency"] = $plan['currency'];

                if ($plan['stripe_required']) {
                    $requirements["{$label} Stripe Price ID"] = $plan['stripe_price_id'];
                }
            }
        }

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
