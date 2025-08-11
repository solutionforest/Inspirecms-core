<x-inspirecms::page.extra-sub-navigation>
    @switch($navigationPageType)
        @case('tree')
            @foreach ($this->getAllCategories() as $cat)
                @php
                    $navTreeKey = 'navigation_tree_' . $cat;
                @endphp
                <x-filament::section>
                    <x-slot name="heading">
                        {{ $cat }}
                    </x-slot>
                    @livewire('inspirecms::navigation-tree', [
                        'category' => $cat,
                        ... $this->getNavigationTreeData($cat),
                    ], key($navTreeKey))
                </x-filament::section>
            @endforeach

            @break
        @default
            
        {{ $this->content }}
        @break

    @endswitch
</x-inspirecms::page.extra-sub-navigation>