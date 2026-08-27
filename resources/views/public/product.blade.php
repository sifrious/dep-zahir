<x-layouts.public :title="$product['name']">
    <x-burd::page-header eyebrow="Product" :title="$product['name']" :subtitle="$product['summary']">
        <x-slot:actions>
            @if (isset($product['documentation']))
                <x-burd::button variant="secondary" :href="route('products.docs', $productKey)">How it works</x-burd::button>
            @endif
            <x-burd::button :href="route('pricing')">Review plans</x-burd::button>
        </x-slot:actions>
    </x-burd::page-header>

    <x-burd::card title="What it does">
        <x-burd::badge>{{ $product['availability'] }}</x-burd::badge>
        @if ($product['name_expansion'])
            <p>
                @foreach ($product['name_expansion'] as $word)
                    <strong>{{ $word['initial'] }}</strong>{{ $word['remainder'] }}{{ $loop->last ? '.' : ' ' }}
                @endforeach
            </p>
        @endif
        <p>{{ $product['description'] }}</p>
    </x-burd::card>

    <section class="site-grid" aria-label="Product details">
        <x-burd::card title="Delivery" variant="inset">
            <p>{{ $product['delivery'] }}</p>
        </x-burd::card>
        <x-burd::card title="Payment security" variant="inset">
            <p>Payments are processed by Stripe using Stripe-hosted Checkout. Accounts does not collect or store complete card numbers.</p>
        </x-burd::card>
    </section>

    <x-burd::card title="Plans">
        <div class="site-grid">
            @foreach ($product['plans'] as $plan)
                <div>
                    <x-burd::badge tone="lion">{{ $plan['name'] }}</x-burd::badge>
                    @if (! $plan['stripe_required'])
                        <p class="site-price">Free</p>
                    @elseif ($plan['price'])
                        <p class="site-price">{{ $plan['price'] }} {{ $plan['currency'] }} <small>per {{ $plan['billing_period'] }}</small></p>
                    @else
                        <p>Pricing is not yet published and this site is not currently presenting an offer for purchase.</p>
                    @endif
                    <ul>
                        @foreach ($plan['features'] as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </x-burd::card>

    <div class="site-actions">
        <x-burd::button variant="secondary" :href="route('billing')">How billing works</x-burd::button>
        <x-burd::button variant="ghost" :href="route('trust.index')">Review policies</x-burd::button>
    </div>
</x-layouts.public>
