@props(['key', 'value'])
@use('Illuminate\View\ComponentAttributeBag')

<div {{ 
    $attributes
        ->grid(['default' => 3, 'md' => 5])
        ->merge(['class' => 'version-diff-item']) 
    }}
>
    <div {{ (new ComponentAttributeBag)->gridColumn(['default' => 1])->class(['version-diff-item-title font-semibold']) }}>{{ $key }}</div>
    <div {{ (new ComponentAttributeBag)->gridColumn(['default' => 2, 'md' => 4]) }} >
        @if (is_array($value))
            <x-inspirecms::version-diff.items :items="$value"/>
        @else
            {!! $value !!}
        @endif
    </d>
</div>