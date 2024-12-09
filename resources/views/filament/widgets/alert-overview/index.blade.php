@php
    $columns = $this->getColumns();
    if (!is_array($columns)) {
        $columns = "grid-cols-{$columns}";
    } else {
        $columns = collect($columns)->map(fn ($column, $grid) => $grid == 'default' ? "grid-cols-{$column}" : "{$grid}:grid-cols-{$column}")->implode(' ');
    }
    $alerts = $this->getCachedAlerts();
@endphp

<div class="fi-wi-alert-overview">
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
</div>

{{-- <div>
    <div wire:loading> 
        Saving post...
    </div>
    <x-filament::loading-indicator class="h-5 w-5" />
    @foreach ($alerts as $alert)
        {{ $alert }}
    @endforeach
</div> --}}