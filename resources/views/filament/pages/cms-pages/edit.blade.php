<x-filament-panels::page
    @class([
        'fi-resource-edit-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>
    @capture($form)
        <x-inspirecms::with-detail-info-container
            formSubmitLiveiwreAction="save"
        >
            <x-slot name="form">
                @if ($this->wrapMainFormBySection())
                    <x-filament::section>
                        {{ $this->form }}
                    </x-filament::section>
                @else
                    {{ $this->form }}
                @endif
            </x-slot>

            @if ($this->hasDetailInfoForm())
                <x-slot name="detailInfoForm">
                    @if ($this->wrapDetailInfoFormBySection())
                        <x-filament::section>
                            {{ $this->detailInfoForm }}
                        </x-filament::section>
                    @else
                        {{ $this->detailInfoForm }}
                    @endif
                </x-slot>
            @endif

            <x-slot name="formSubmitAction">
                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />
            </x-slot>
                    
        </x-inspirecms::with-detail-info-container>
    @endcapture

    @php
        $relationManagers = $this->getRelationManagers();
        $hasCombinedRelationManagerTabsWithContent = $this->hasCombinedRelationManagerTabsWithContent();
    @endphp

    @if ((! $hasCombinedRelationManagerTabsWithContent) || (! count($relationManagers)))
        {{ $form() }}
    @endif

    @if (count($relationManagers))
        <x-filament-panels::resources.relation-managers
            :active-locale="isset($activeLocale) ? $activeLocale : null"
            :active-manager="$this->activeRelationManager ?? ($hasCombinedRelationManagerTabsWithContent ? null : array_key_first($relationManagers))"
            :content-tab-label="$this->getContentTabLabel()"
            :content-tab-icon="$this->getContentTabIcon()"
            :content-tab-position="$this->getContentTabPosition()"
            :managers="$relationManagers"
            :owner-record="$record"
            :page-class="static::class"
        >
            @if ($hasCombinedRelationManagerTabsWithContent)
                <x-slot name="content">
                    {{ $form() }}
                </x-slot>
            @endif
        </x-filament-panels::resources.relation-managers>
    @endif

    <x-filament-panels::page.unsaved-data-changes-alert />
</x-filament-panels::page>
