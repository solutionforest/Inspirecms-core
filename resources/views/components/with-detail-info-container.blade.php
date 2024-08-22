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
                <div class="lg:flex-1">
                    {{ $detailInfoForm }}
                </div>
            @endif

            <div @class([
                'lg:flex-[2_2_10%]' => $haveDetailInfoForm,
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
