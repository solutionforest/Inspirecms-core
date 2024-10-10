@php
    $formKey = $this->getId() . '.forms.' . $this->getFormStatePathFor('uploadFileForm');
@endphp
<x-inspirecms-support::tree-node class="media-library" x-data="{
    selectedMediaId: $wire.entangle('selectedMediaId').live,
    selectMedia: function (mediaId) {
        this.selectedMediaId = mediaId;
    },
}">
    Media Library Items on this level [todo]
    
    <x-slot:mainViewContent>
        <div class="pb-2 flex gap-2 justify-end">
            <x-filament::button 
                size="md" 
                wire:click="mountAction('createFolder')"
            >
                Create Folder
            </x-filament::button>
        </div>

        <div class="uploadform-container pb-2">
            <form 
                wire:key="{{$formKey}}"
                wire:submit="saveUploadFile"
            >
                {{ $this->uploadFileForm }}
                
                <div class="media-library__form__actions pt-2">
                    <div class="fi-ac gap-3 flex flex-wrap items-center flex-row-reverse">
                        <x-filament::button 
                            class="media-library__form__actions__button"
                            size="md" 
                            type="submit">
                            Upload
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </div>

        <div class="media-library__breadcrumbs my-5">
            <x-inspirecms::media-library.breadcrumbs :breadcrumbs="$this->getBreadcrumbs()" />
        </div>

        <div class="media-content-container">
            <div class="media-library__content">
                <x-filament::grid class="media-library__content__items"
                    :default="$this->selectedMedia ? 1 : 2"
                    2xl="9"
                    :xl="$this->selectedMedia ? 4 : 6"
                    :lg="$this->selectedMedia ? 2 : 4"
                    :md="$this->selectedMedia ? 2 : 3"
                >
                    @foreach ($mediaItems as $mediaItem)
                        <div class="media-library__content__items__item">
                            <div @class([
                                    'media-library__content__items__item__thumb',
                                    'ring-1 ring-gray-200/50 dark:ring-gray-400/50 flex justify-center items-center' => ! $mediaItem->isImage(),
                                ])
                                @style([
                                    \Filament\Support\get_color_css_variables('primary', [400, 500]),
                                ])
                                :class="{ 'selected': selectedMediaId === '{{ $mediaItem->getKey() }}' }"
                                @click="selectMedia('{{ $mediaItem->getKey() }}')"
                                @if ($mediaItem->isFolder())
                                    @dblclick="$wire.openFolder('{{ $mediaItem->getKey() }}')"
                                @endif
                            >
                                @if ($mediaItem->isImage())
                                    <img src="{{ $mediaItem->getThumbnailUrl() }}" alt="{{ $mediaItem->title }}" />
                                @else
                                    <x-filament::icon 
                                        :icon="$mediaItem->getThumbnail()" 
                                        class="media-library__content__items__item__thumb__icon"
                                    />
                                @endif
                            </div>
                            <span class="media-library__content__items__item__title">
                                {{ $mediaItem->title }}
                            </span>
                        </div>
                    @endforeach
                </x-filament::grid>
            </div>
            @if ($this->selectedMedia)
                <x-inspirecms::media-library.detail-info :mediaItem="$this->selectedMedia" />
            @endif
            <x-filament-actions::modals />
        </div>
    </x-slot:mainViewContent>
</x-inspirecms-support::tree-node>