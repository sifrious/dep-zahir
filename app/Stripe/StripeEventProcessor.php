<?php

namespace App\Stripe;

use App\Models\EntitlementGrant;
use App\Models\Product;
use App\Models\StripeCustomer;
use App\Models\StripeEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Stripe\Event;
use Stripe\Subscription;

final readonly class StripeEventProcessor
{
    public function process(Event $event, string $payloadHash): void
    {
        DB::transaction(function () use ($event, $payloadHash): void {
            if (StripeEvent::query()->whereKey($event->id)->exists()) {
                return;
            }

            if (in_array($event->type, [
                'customer.subscription.created',
                'customer.subscription.updated',
                'customer.subscription.deleted',
            ], true) && $event->data->object instanceof Subscription) {
                $this->applySubscription($event->data->object);
            }

            StripeEvent::query()->create([
                'id' => $event->id,
                'type' => $event->type,
                'livemode' => $event->livemode,
                'occurred_at' => Carbon::createFromTimestamp($event->created),
                'processed_at' => now(),
                'payload_sha256' => $payloadHash,
            ]);
        });
    }

    private function applySubscription(Subscription $subscription): void
    {
        $customerId = is_string($subscription->customer) ? $subscription->customer : $subscription->customer->id;
        $stripeCustomer = StripeCustomer::query()->where('customer_id', $customerId)->first();

        if ($stripeCustomer === null) {
            return;
        }

        EntitlementGrant::query()
            ->where('source', 'stripe')
            ->where('source_reference', $subscription->id)
            ->update(['revoked_at' => now()]);

        if (! in_array($subscription->status, config('services.stripe.active_subscription_statuses'), true)) {
            return;
        }

        foreach ($subscription->items->data as $item) {
            $definition = collect(config('services.stripe.prices'))
                ->firstWhere('price_id', $item->price->id);

            if ($definition === null) {
                continue;
            }

            $product = Product::query()->firstOrCreate(
                ['key' => $definition['product']],
                ['name' => $definition['product_name']],
            );

            EntitlementGrant::query()->updateOrCreate(
                [
                    'account_id' => $stripeCustomer->account_id,
                    'product_id' => $product->id,
                    'entitlement' => $definition['entitlement'],
                    'source' => 'stripe',
                    'source_reference' => $subscription->id,
                ],
                [
                    'starts_at' => now(),
                    'expires_at' => null,
                    'revoked_at' => null,
                ],
            );
        }
    }
}
