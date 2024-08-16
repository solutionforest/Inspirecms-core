@props([
    'form', 
    'formSubmitLiveiwreAction',
    'formSubmitAction' => null,
    'detailInfoForm' => null,
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
                'flex flex-col lg:flex-row-reverse px-0.5 gap-4' => $haveDetailInfoForm,
            ])
        >
            @if ($haveDetailInfoForm)
                <div class="w-full flex-col lg:w-1/3">
                    {{ $detailInfoForm }}
                </div>
            @endif

            <div @class([
                'w-full',
                'flex flex-col lg:w-2/3 gap-3' => $haveDetailInfoForm,
            ])>
                {{ $form }}
            </div>
        </div>

        @if ($formSubmitAction)
            <div>
                {{ $formSubmitAction }}
            </div>
        @endif

    </x-filament-panels::form>

</div>