@php
    $columns = $this->getColumns();
    if (!is_array($columns)) {
        $columns = "grid-cols-{$columns}";
    } else {
        $columns = collect($columns)->map(fn ($column, $grid) => $grid == 'default' ? "grid-cols-{$column}" : "{$grid}:grid-cols-{$column}")->implode(' ');
    }
    $alerts = $this->getCachedAlerts();
@endphp

<x-filament-widgets::widget class="fi-wi-alert-overview">
    @if (count($alerts) > 0)
        <div
            @class([
                'fi-wi-alert-overview-alerts-ctn flex grid gap-6',
                $columns,
            ])
        >
            @foreach ($alerts as $alert)
                {{ $alert }}
            @endforeach
        </div>
    @endif
</x-filament-widgets::widget>