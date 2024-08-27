@php
    $revertBreakPoint = $getRevertBreakPoint();
@endphp
<div
    {{
        $attributes
            ->merge([
                'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)
            ->class([
                'fi-fo-revert-order-group flex gap-4',
                match ($revertBreakPoint) {
                    'lg' => 'flex-col lg:flex-row-reverse',
                    'md' => 'flex-col md:flex-row-reverse',
                    'sm' => 'flex-col sm:flex-row-reverse',
                    default => 'flex-col',
                },
            ])
    }}
    @foreach ($getChildComponentContainers() as $container)
        @foreach ($container->getComponents() as $component)
            <div
                @class([
                    match ($revertBreakPoint) {
                        'lg' => 'lg:flex-[2_2_10%]',
                        'md' => 'md:flex-[2_2_10%]',
                        'sm' => 'sm:flex-[2_2_10%]',
                        default => 'flex-[2_2_10%]',
                    } => $component->canGrow(),
                    match ($revertBreakPoint) {
                        'lg' => 'lg:flex-1',
                        'md' => 'md:flex-1',
                        'sm' => 'sm:flex-1',
                        default => 'flex-1',
                    } => ! $component->canGrow(),
                ])
            >
                {{ $component }}
            </div>
        @endforeach
    @endforeach
</div>