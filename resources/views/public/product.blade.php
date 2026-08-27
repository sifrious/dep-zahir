<x-layouts.public :title="$product['name']">
    <article>
        <p class="eyebrow">Product</p>
        <h1>{{ $product['name'] }}</h1>
        <p>{{ $product['summary'] }}</p>
        <p>{{ $product['description'] }}</p>

        <h2>Delivery</h2>
        <p>{{ $product['delivery'] }}</p>

        <h2>Price</h2>
        @if ($product['price'])
            <p>{{ $product['price'] }} {{ $product['currency'] }} per {{ $product['billing_period'] }}.</p>
        @else
            <p>Pricing is not yet published and this site is not currently presenting an offer for purchase.</p>
        @endif

        <h2>Payment security</h2>
        <p>Payments are processed by Stripe using Stripe-hosted Checkout. This service does not collect or store complete card numbers.</p>

        <h2>Policies</h2>
        <p><a href="{{ route('trust.index') }}">Review legal, privacy, refund, cancellation, and compliance information.</a></p>
    </article>
</x-layouts.public>
