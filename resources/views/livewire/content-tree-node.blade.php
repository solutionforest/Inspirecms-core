<div class="flex flex-col space-y-2">
    <x-filament::input.wrapper>
        <x-filament::input
            type="text"
            wire:model.live="search"
            placeholder="Search..."
        />
    </x-filament::input.wrapper>
    <div wire:loading wire:target="search"> 
        Searching...
    </div>
    @if (count($records) > 0)
        <x-inspirecms::tree.nested-checkbox-tree
            :items="$records"
            :modelable="$modelable"
            :max="$limits['max'] ?? null"
            :min="$limits['min'] ?? null"
            collapsable
        />
    @else
        <p>No records found.</p>
    @endif
</div>