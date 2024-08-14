<x-filament-panels::page
    @class([
        'fi-resource-create-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <x-inspirecms-core::with-detail-info-container
        formSubmitLiveiwreAction="create"
        :wrapMainFormBySection="$this->wrapMainFormBySection()"
    >
        <x-slot name="form">
            {{ $this->form }}
        </x-slot>

        @if ($this->hasDetailInfoForm())
            <x-slot name="detailInfoForm">
                {{ $this->detailInfoForm }}
            </x-slot>
        @endif

        <x-slot name="formSubmitAction">
            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-slot>
                
    </x-inspirecms-core::with-detail-info-container>

    <x-filament-panels::page.unsaved-data-changes-alert />

</x-filament-panels::page>