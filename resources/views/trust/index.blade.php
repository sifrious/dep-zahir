<x-layouts.public title="Trust center">
    <x-burd::page-header
        eyebrow="Public record"
        title="Trust center"
        subtitle="Legal, commerce, security, accessibility, and compliance information for Accounts and every connected product."
    />

    @foreach (collect($documents)->groupBy('category') as $category => $categoryDocuments)
        <x-burd::card :title="$category">
            <x-burd::record-list :label="$category.' documents'">
                @foreach ($categoryDocuments as $slug => $document)
                    <x-burd::record-list-item :href="$document['published'] ? route('trust.show', $slug) : (app()->environment(['local', 'testing']) && isset($document['content_path']) ? route('trust.draft', $slug) : null)">
                        {{ $document['title'] }}
                        <x-slot:meta>
                            @if ($document['published'])
                                <x-burd::badge tone="bird">Published · {{ $document['version'] }}</x-burd::badge>
                            @elseif (app()->environment(['local', 'testing']) && isset($document['content_path']))
                                <x-burd::badge tone="lion">Draft preview</x-burd::badge>
                            @else
                                <x-burd::badge>Awaiting approved content</x-burd::badge>
                            @endif
                        </x-slot:meta>
                    </x-burd::record-list-item>
                @endforeach
            </x-burd::record-list>
        </x-burd::card>
    @endforeach
</x-layouts.public>
