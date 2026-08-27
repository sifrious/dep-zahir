<x-layouts.public title="Products">
    <x-burd::page-header
        :eyebrow="$business['name'] ?: 'Business name pending publication'"
        title="Software that turns plans into working systems."
        subtitle="Explore our hosted products, pricing, billing practices, and the public policies that govern their use."
    >
        <x-slot:actions>
            <x-burd::button :href="route('pricing')">Review pricing</x-burd::button>
            <x-burd::button variant="secondary" :href="route('trust.index')">Read policies</x-burd::button>
        </x-slot:actions>
    </x-burd::page-header>

    <section class="site-grid" aria-label="Products">
        @foreach ($products as $slug => $product)
            <x-burd::card :title="$product['name']" eyebrow="Hosted product">
                <p>{{ $product['summary'] }}</p>
                <div class="site-actions">
                    <x-burd::button size="sm" :href="route('products.show', $slug)">Explore {{ $product['name'] }}</x-burd::button>
                    @if (isset($product['documentation']))
                        <x-burd::button size="sm" variant="secondary" :href="route('products.docs', $slug)">How it works</x-burd::button>
                    @endif
                    <x-burd::button size="sm" variant="ghost" :href="route('pricing')">View plans</x-burd::button>
                </div>
            </x-burd::card>
        @endforeach
    </section>

    <x-burd::card title="Customer support" eyebrow="Direct contact" variant="inset">
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
    </x-burd::card>
</x-layouts.public>
