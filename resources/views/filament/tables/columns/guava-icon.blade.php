@props(['height' => 6])
@php
    if ($height <= 0) {
        $height = 6;
    }
@endphp
<div 
    {{
        $attributes
            ->merge($getExtraAttributes(), escape: false)
            ->class([
                'filament-icon-picker-icon-column px-4 py-3',
                'px-3 py-4' => ! $isInline(),
            ])
    }}
>
	@if($icon = $getState())
        <div @class(["h-$height"])>
            <x-icon 
                name="{{$icon}}" 
                class="h-full"
            />
        </div>
	@endif
</div>