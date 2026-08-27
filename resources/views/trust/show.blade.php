<x-layouts.public :title="$document['title']">
    <article>
        <h1>{{ $document['title'] }}</h1>
        <p class="meta">Version {{ $document['version'] }} · Effective {{ $document['effective_at'] }}</p>
        {!! str($document['content'])->markdown() !!}
    </article>
</x-layouts.public>
