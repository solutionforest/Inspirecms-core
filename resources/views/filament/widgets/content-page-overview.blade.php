@php
    // todo: add translation
@endphp
<x-filament-widgets::widget class="fi-wi-content-page-overview">
    <x-filament::grid lg="3" class="gap-6">
        @php
            $defaultPage = $this->getDefaultPageRecord();
            $defaultPageIcon = $this->getContentStatusIcon($defaultPage);
            $defaultPageStatusColor = $this->getContentStatusColor($defaultPage);
            $defaultPageUrl = $this->getDefaultPageUrl();
        @endphp
        @if (filled($defaultPageUrl))
            <a href="{{ $defaultPageUrl }}">
                <x-filament::section :icon="$defaultPageIcon" :icon-color="$defaultPageStatusColor" icon-size="lg">
                    <x-slot name="heading">
                        Default page
                    </x-slot>
                    <x-slot name="description">
                        View default page
                    </x-slot>
                    <div>
                        <p class="text-md">
                            {{ $this->getContentTitle($defaultPage) ?? 'No record' }}
                        </p>
                        @if ($defaultPage)
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-sm text-gray-400 dark:text-gray-200/80">Publish at</span>
                                <x-filament::badge icon="heroicon-o-clock" icon-position="after" color="secondary">
                                    {{ $this->getContentPublishTime($defaultPage) }}
                                </x-filament::badge>
                            </div>
                        @endif
                    </div>
                </x-filament::section>
            </a>
        @endif
        @if ($this->canCreateContent())
            <x-filament::section class="cursor-pointer" wire:click="callAction('create_content')">
                <x-slot name="heading">
                    Create content
                </x-slot>
                <x-slot name="description">
                    Create new content
                </x-slot>
                <span>
                    Use this section to create new content for your content management system. This allows you to add fresh and relevant information to your site, keeping it up-to-date and engaging for your audience.
                </span>
            </x-filament::section>
        @endif
        @php
            $createDocumentUrl = $this->getCreateDocumentUrl();
        @endphp
        @if (filled($createDocumentUrl))
            <a href="{{ $createDocumentUrl }}">
                <x-filament::section>
                    <x-slot name="heading">
                        Create document type
                    </x-slot>
                    <x-slot name="description">
                        Create new document type
                    </x-slot>
                    <span>
                        Use this section to create a new document type for your content management system. This allows you to define custom structures and fields for different types of documents, ensuring that your content is organized and easily manageable.
                    </span>
                </x-filament::section>
            </a>
        @endif
    </x-filament::grid>
</x-filament-widgets::widget>