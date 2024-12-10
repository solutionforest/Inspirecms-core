<x-filament-widgets::widget class="fi-wi-content-page-overview">
    @php
        $defaultPage = $this->getDefaultPageRecord();
        $defaultPageIcon = $this->getContentStatusIcon($defaultPage);
        $defaultPageStatusColor = $this->getContentStatusColor($defaultPage);
        $haveStatusColor = isset($defaultPageStatusColor) && filled($defaultPageStatusColor);
    @endphp
    <a class="card main" href="{{ $this->getDefaultPageUrl() }}">
        @if ($defaultPageIcon)
            <div 
                @style([
                    "--icon-container-color:var(--{$defaultPageStatusColor}-400)" => $haveStatusColor,
                ]) 
                @class([
                    'icon-container',
                    'icon-container--custom-color' => $haveStatusColor,
                ])
            >
                <x-filament::icon :icon="$defaultPageIcon" class="icon" />
            </div>
        @endif
        <h2 class="text-primary-600 dark:text-primary-400">Default Page</h2>
        <div>
            <p class="text-md">
                {{ $this->getContentTitle($defaultPage) }}
            </p>
            <div class="mt-2 flex items-center gap-2">
                <span class="text-sm text-gray-400 dark:text-gray-200/80">Publish at</span>
                <x-filament::badge icon="heroicon-o-clock" icon-position="after" color="secondary">
                    {{ $this->getContentPublishTime($defaultPage) }}
                </x-filament::badge>
            </div>
        </div>
    </a>
    <button class="card" wire:click="callAction('create_content')">
        Create content
    </button>
    <a class="card" href="{{ $this->getCreateDocumentUrl() }}">
        Create document type
    </a>
</x-filament-widgets::widget>