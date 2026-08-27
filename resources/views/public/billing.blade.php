<x-layouts.public title="Billing">
    <x-burd::page-header
        eyebrow="All products"
        title="Billing"
        subtitle="One billing system and one set of commerce disclosures for every connected product."
    />

    <section class="site-grid" aria-label="Billing practices">
        <x-burd::card title="Subscriptions">
            <p>Recurring plans are billed in advance at the price, currency, and interval shown before checkout.</p>
        </x-burd::card>
        <x-burd::card title="Payments">
            <p>Stripe-hosted Checkout collects payment details. Accounts stores Stripe identifiers and subscription state, not complete card numbers.</p>
        </x-burd::card>
        <x-burd::card title="Invoices and receipts">
            <p>Stripe supplies transaction receipts and subscription invoices using the billing contact associated with the Stripe customer.</p>
        </x-burd::card>
        <x-burd::card title="Taxes">
            <p>Applicable taxes are disclosed during checkout according to the purchaser and product configuration.</p>
        </x-burd::card>
    </section>

    <x-burd::card title="Published plans" eyebrow="Central catalog">
        <x-burd::record-list label="Product billing plans">
            @foreach ($products as $slug => $product)
                @foreach ($product['plans'] as $plan)
                    <x-burd::record-list-item :href="route('products.show', $slug)">
                        {{ $product['name'] }} · {{ $plan['name'] }}
                        <x-slot:meta>
                            @if (! $plan['stripe_required'])
                                Free
                            @elseif ($plan['price'])
                                {{ $plan['price'] }} {{ $plan['currency'] }} / {{ $plan['billing_period'] }}
                            @else
                                Price pending
                            @endif
                        </x-slot:meta>
                    </x-burd::record-list-item>
                @endforeach
            @endforeach
        </x-burd::record-list>
    </x-burd::card>

    <x-burd::card title="Manage billing" variant="inset">
        <p>Authenticated customers will use the Stripe Billing Portal to review invoices, update payment methods, and cancel eligible subscriptions. The portal entry point will appear after Accounts login is enabled.</p>
    </x-burd::card>

    <div class="site-actions">
        <x-burd::button variant="secondary" :href="route('trust.index')">Refund and cancellation policies</x-burd::button>
        @if ($business['support_email'])
            <x-burd::button variant="ghost" href="mailto:{{ $business['support_email'] }}">Contact billing support</x-burd::button>
        @endif
    </div>
</x-layouts.public>
