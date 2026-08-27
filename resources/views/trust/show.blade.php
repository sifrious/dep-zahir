<x-layouts.public :title="$document['title']">
    <x-burd::page-header
        :eyebrow="$draft ? 'Draft preview · Not in effect' : 'Published policy'"
        :title="$document['title']"
        :subtitle="$draft ? 'Review and replace every required decision marker before publication.' : 'Version '.$document['version'].' · Effective '.$document['effective_at']"
    />

    @if ($draft)
        <x-burd::alert tone="warning" title="Draft policy">
            This page is available only in the local environment. It is not approved, published, or in effect.
        </x-burd::alert>
    @endif

    <article class="burg-card site-policy">
        {!! str($document['content'])->markdown() !!}
    </article>
</x-layouts.public>
