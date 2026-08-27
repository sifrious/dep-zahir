<x-layouts.public :title="$product['name'].' documentation'">
    <x-burd::page-header
        eyebrow="Product documentation"
        :title="$product['name'].' behavior'"
        :subtitle="$product['documentation']['purpose']"
    >
        <x-slot:actions>
            <x-burd::button variant="secondary" :href="route('products.show', $productKey)">Product overview</x-burd::button>
            <x-burd::button variant="ghost" :href="route('pricing')">Pricing</x-burd::button>
        </x-slot:actions>
    </x-burd::page-header>

    <section class="site-grid" aria-label="Documentation status">
        <x-burd::card title="Availability" variant="inset">
            <x-burd::badge>{{ $product['availability'] }}</x-burd::badge>
            <p>{{ $product['documentation']['status'] }}</p>
        </x-burd::card>
        <x-burd::card title="Billing entitlement" variant="inset">
            <p><code>{{ collect($product['plans'])->first()['entitlement'] }}</code></p>
            <p>Accounts is the authority for whether an account may use this product.</p>
        </x-burd::card>
    </section>

    <x-burd::card title="Who it is for">
        <ul>
            @foreach ($product['documentation']['audiences'] as $audience)
                <li>{{ $audience }}</li>
            @endforeach
        </ul>
    </x-burd::card>

    <section class="site-grid" aria-label="Product inputs and outputs">
        <x-burd::card title="Inputs">
            <ul>
                @foreach ($product['documentation']['inputs'] as $input)
                    <li>{{ $input }}</li>
                @endforeach
            </ul>
        </x-burd::card>
        <x-burd::card title="Outputs">
            <ul>
                @foreach ($product['documentation']['outputs'] as $output)
                    <li>{{ $output }}</li>
                @endforeach
            </ul>
        </x-burd::card>
    </section>

    <x-burd::card title="MVP workflow" eyebrow="From request to result">
        <ol>
            @foreach ($product['documentation']['workflow'] as $step)
                <li>
                    <strong>{{ $step['name'] }}</strong>
                    <p>{{ $step['description'] }}</p>
                </li>
            @endforeach
        </ol>
    </x-burd::card>

    <section class="site-grid" aria-label="Product behavior">
        <x-burd::card title="Caller interactions">
            <ul>
                @foreach ($product['documentation']['interactions'] as $interaction)
                    <li>{{ $interaction }}</li>
                @endforeach
            </ul>
        </x-burd::card>
        <x-burd::card title="Execution agents">
            <p>Agents are interchangeable through a shared execution-agent contract.</p>
            <ul>
                @foreach ($product['documentation']['agents'] as $agent)
                    <li>{{ $agent }}</li>
                @endforeach
            </ul>
        </x-burd::card>
    </section>

    <x-burd::card title="Data and service boundaries">
        <ul>
            @foreach ($product['documentation']['data_boundaries'] as $boundary)
                <li>{{ $boundary }}</li>
            @endforeach
        </ul>
    </x-burd::card>

    <x-burd::card title="Current limits" variant="inset">
        <ul>
            @foreach ($product['documentation']['limits'] as $limit)
                <li>{{ $limit }}</li>
            @endforeach
        </ul>
    </x-burd::card>
</x-layouts.public>
