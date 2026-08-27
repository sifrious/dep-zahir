<x-layouts.public title="Trust center">
    <h1>Trust center</h1>
    <p>Legal, policy, security, accessibility, and compliance information for Accounts and connected products.</p>

    @foreach (collect($documents)->groupBy('category') as $category => $categoryDocuments)
        <section aria-labelledby="{{ str($category)->slug() }}">
            <h2 id="{{ str($category)->slug() }}">{{ $category }}</h2>
            <ul>
                @foreach ($categoryDocuments as $slug => $document)
                    <li>
                        @if ($document['published'])
                            <a href="{{ route('trust.show', $slug) }}">{{ $document['title'] }}</a>
                            <div class="meta">Version {{ $document['version'] }} · Effective {{ $document['effective_at'] }}</div>
                        @else
                            <span>{{ $document['title'] }}</span>
                            <div class="meta">Awaiting approved publication</div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach
</x-layouts.public>
