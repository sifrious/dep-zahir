<x-layouts.public title="Pricing">
    <x-burd::page-header
        eyebrow="All products"
        title="Pricing"
        subtitle="Every public product and recurring plan is listed here with its purchase currency and billing interval."
    />

    @foreach ($products as $slug => $product)
        <section class="site-stack" aria-label="{{ $product['name'] }} pricing">
            <x-burd::page-header :level="2" :title="$product['name']" :subtitle="$product['summary']">
                <x-slot:actions>
                    <x-burd::button size="sm" variant="ghost" :href="route('products.show', $slug)">Product details</x-burd::button>
                </x-slot:actions>
            </x-burd::page-header>
            <x-burd::badge>{{ $product['availability'] }}</x-burd::badge>
            <div class="site-grid">
                @foreach ($product['plans'] as $plan)
                    <x-burd::card :title="$product['name'].' · '.$plan['name']" :level="3">
                        @if (! $plan['stripe_required'])
                            <p class="site-price">Free</p>
                        @elseif ($plan['price'])
                            <p class="site-price">{{ $plan['price'] }} {{ $plan['currency'] }} <small>per {{ $plan['billing_period'] }}</small></p>
                        @else
                            <x-burd::badge>Price pending publication</x-burd::badge>
                        @endif
                        <ul>
                            @foreach ($plan['features'] as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        @if ($plan['stripe_required'])
                            <p><small>Taxes, if applicable, are calculated during Stripe Checkout.</small></p>
                        @else
                            <p><small>No payment method is required for the free tier.</small></p>
                        @endif
                    </x-burd::card>
                @endforeach
            </div>
        </section>
    @endforeach

    <x-burd::card title="Billing terms" variant="inset">
        <p>Prices, currencies, and billing intervals are displayed before purchase. Review billing, cancellation, refund, and delivery information before starting a subscription.</p>
        <x-burd::button size="sm" variant="secondary" :href="route('billing')">Review billing</x-burd::button>
    </x-burd::card>
</x-layouts.public>
