<x-filament-panels::page
    @class([
        'fi-resource-create-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <x-inspirecms::with-detail-info-container
        formSubmitLiveiwreAction="create"
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

    <x-filament-panels::page.unsaved-data-changes-alert />

</x-filament-panels::page>