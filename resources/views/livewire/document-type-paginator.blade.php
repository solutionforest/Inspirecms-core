<div class="flex flex-col gap-4 h-full">
    <x-filament::input.wrapper>
        <x-filament::input
            wire:model.live.debounce.3000s="search"
            :placeholder="@trans('inspirecms::inspirecms.search.placeholder')"
        />
    </x-filament::input.wrapper>

    <div class="flex gap-2 flex-col flex-1"
        wire:loading.flex 
        wire:target="{{ $loadingTargets }}"
    >  
        @for ($i = 0; $i < 5; $i++)
            <x-filament::loading-section height="4rem" />
        @endfor
    </div>

    <ul class="flex gap-2 flex-col flex-1" 
        wire:loading.remove 
        wire:target="{{ $loadingTargets }}"
    >
        @forelse ($paginator as $item)
            <li>
                <x-filament::button
                    :icon="$item['icon'] ?? null"
                    :label="$item['rawLabel'] ?? null"
                    color="gray"
                    tag="a"
                    href="{{ $item['url'] ?? null }}"
                    size="xl"
                    class="w-full !justify-start"
                >
                    {{ $item['displayLabel'] ?? $item['rawLabel'] ?? null }}
                </x-filament::button>
            </li>
        @empty
            <li>
                <p class="text-gray-500 dark:text-gray-400">
                    {{ __('inspirecms::inspirecms.search.no_results') }}
                </p>
            </li>
        @endforelse
    </ul>
    <x-filament::pagination
        :paginator="$paginator"
        current-page-option-property="perPage"
        :page-options="[5, 10, 20, 50, 100, 'all']"
        extreme-links
    />
</div>