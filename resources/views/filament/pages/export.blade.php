<x-filament-panels::page>
    @foreach ($this->getTableComponents() as $item)
        @livewire($item['component'], $item['data'])
    @endforeach
</x-filament-panels::page>
