@php
    if ((! isset($columnSpan)) || (! is_array($columnSpan))) {
        $columnSpan = [
            'default' => $columnSpan ?? null,
        ];
    }

    if ((! isset($columnStart)) || (! is_array($columnStart))) {
        $columnStart = [
            'default' => $columnStart ?? null,
        ];
    }

    $height ??= '8rem';
@endphp

<div
    {{
        ($attributes ?? new \Illuminate\View\ComponentAttributeBag)
            ->gridColumn($columnSpan, $columnStart)
            ->class(['fi-loading-bar'])
    }}
>
    <div class="animate-pulse w-full rounded-lg bg-black/20 dark:bg-gray-200/20" style="height: {{ $height }}"></div>
</div>