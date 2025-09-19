<x-filament-panels::page>
    
    @foreach ($this->getAllCategories() as $category)
        @php
            $livewireData = [
                'category' => $category,
                ...$this->getNavigationTreeData($category) 
            ];

            $livewireId = "navigation-tree-{$category}";
        @endphp
        <x-filament::section>
            <x-slot name="heading">
                {{ ucfirst($category) }}
            </x-slot>

            @livewire('inspirecms::navigation-tree', $livewireData, key($livewireId))
        </x-filament::section>
    @endforeach

    <x-filament-actions::modals />
    
</x-filament-panels::page>
