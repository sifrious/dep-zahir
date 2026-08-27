<x-layouts.public title="Products">
    <section>
        <p class="eyebrow">{{ $business['name'] ?: 'Business name pending publication' }}</p>
        <h1>Software that turns plans into working systems.</h1>
        <p>Explore our hosted products, their delivery terms, and the public policies that govern their use.</p>
    </section>

    <section aria-labelledby="products-heading">
        <h2 id="products-heading">Products</h2>
        <ul>
            @foreach ($business['products'] as $slug => $product)
                <li>
                    <h3><a href="{{ route('products.show', $slug) }}">{{ $product['name'] }}</a></h3>
                    <p>{{ $product['summary'] }}</p>
                    @if ($product['price'])
                        <p class="meta">{{ $product['price'] }} {{ $product['currency'] }} per {{ $product['billing_period'] }}</p>
                    @else
                        <p class="meta">Pricing is not yet published.</p>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>

    <section aria-labelledby="support-heading">
        <h2 id="support-heading">Customer support</h2>
        @if ($business['support_email'])
            <p>Email <a href="mailto:{{ $business['support_email'] }}">{{ $business['support_email'] }}</a>.</p>
        @else
            <p>Customer-service contact details are pending publication.</p>
        @endif
        @if ($business['support_phone'])
            <p>Call <a href="tel:{{ $business['support_phone'] }}">{{ $business['support_phone'] }}</a>.</p>
        @endif
        @if ($business['address'])
            <address>{{ $business['address'] }}</address>
        @endif
    </section>
</x-layouts.public>
