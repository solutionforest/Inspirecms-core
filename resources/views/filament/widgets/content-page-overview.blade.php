<x-filament-widgets::widget class="fi-wi-content-page-overview">
    <div 
        {{ 
            (new \Illuminate\View\ComponentAttributeBag) 
                ->grid(['lg' => 3])
                ->class(['gap-6'])
        }}
    >
        @php
            $defaultPage = $this->getDefaultPageRecord();
            $defaultPageIcon = $this->getContentStatusIcon($defaultPage);
            $defaultPageStatusColor = $this->getContentStatusColor($defaultPage);
            $defaultPageUrl = $this->getDefaultPageUrl();
        @endphp
        @if (filled($defaultPageUrl))
            <a href="{{ $defaultPageUrl }}">
                <x-filament::section 
                    :icon="$defaultPageIcon" 
                    :icon-color="$defaultPageStatusColor" 
                    icon-size="lg"
                    :heading="__('inspirecms::widgets.content_page_overview.default_page.title')"
                    heading-tag="h3"
                    :description="__('inspirecms::widgets.content_page_overview.default_page.description')"
                >
                    <div>
                        <p class="text-md">
                            {{ $this->getContentTitle($defaultPage) ?? __('inspirecms::inspirecms.n/a') }}
                        </p>
                        @if ($defaultPage)
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-sm text-gray-400 dark:text-gray-200/80">{{ __('inspirecms::inspirecms.publish_at_xxx', ['time' => '']) }}</span>
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
            <x-filament::section 
                class="cursor-pointer" 
                wire:click="callAction('createContent')"
                :heading="__('inspirecms::widgets.content_page_overview.create_content.title')"
                heading-tag="h3"
                :description="__('inspirecms::widgets.content_page_overview.create_content.description')"
            >
                <p>
                    {{ __('inspirecms::widgets.content_page_overview.create_content.message') }}
                </p>
            </x-filament::section>
        @endif
        @php
            $createDocumentUrl = $this->getCreateDocumentUrl();
        @endphp
        @if (filled($createDocumentUrl))
            <a href="{{ $createDocumentUrl }}">
                <x-filament::section
                    :heading="__('inspirecms::widgets.content_page_overview.create_document_type.title')"
                    heading-tag="h3"
                    :description="__('inspirecms::widgets.content_page_overview.create_document_type.description')" 
                >
                    <p>
                    {{ __('inspirecms::widgets.content_page_overview.create_document_type.message') }}
                    </p>
                </x-filament::section>
            </a>
        @endif
    </div>
</x-filament-widgets::widget>