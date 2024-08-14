@props([
    'form', 
    'formSubmitLiveiwreAction',
    'formSubmitAction' => null,
    'detailInfoForm' => null,
    'wrapMainFormBySection' => true,
])

@php
    $haveDetailInfoForm = $detailInfoForm != null;
@endphp

<div class="page-form-container">
    
    <x-filament-panels::form
        id="form"
        :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
        :wire:submit="$formSubmitLiveiwreAction"
    >
        <div 
            @class([
                'w-full',
                'flex flex-col lg:flex-row-reverse px-0.5 gap-2' => $haveDetailInfoForm,
            ])
        >

            @if ($haveDetailInfoForm)
                <div class="w-full flex-col lg:w-1/3">
                    <x-filament::section>
                        {{ $detailInfoForm }}
                    </x-filament::section>
                </div>
            @endif

            @if ($wrapMainFormBySection)
                <x-filament::section 
                    @class([
                        'w-full',
                        'flex flex-col lg:w-2/3 gap-3' => $haveDetailInfoForm,
                    ])
                >
                    {{ $form }}
                </x-filament::section>
            @else
                {{ $form }}
            @endif
        </div>

        @if ($formSubmitAction)
            <div>
                {{ $formSubmitAction }}
            </div>
        @endif

    </x-filament-panels::form>

</div>