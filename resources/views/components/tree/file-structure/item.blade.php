@props([
    'key',
    'depth',
    'title',
    'icon' => null,
    'iconAlias' => null,
    'children' => [],
    'groupKey' => null,
])

<div class="py-0.5 flex items-center gap-x-0.5 w-full">
    <div class="grow px-1.5 rounded-md">
        <div 
            class="flex items-center gap-x-3"
            data-treenode=@js($key)
            @if ($groupKey != null)
                data-treenode-group=@js($groupKey)
            @endif
        >
            @if ($icon || $iconAlias)
                <x-filament::icon
                    :alias="$iconAlias"
                    :icon="$icon"
                    class="fi-icon h-5 w-5 text-gray-500 dark:text-gray-100"
                />
            @endif

            <div class="grow">
                <span class="text-sm font-mono text-gray-800 dark:text-gray-100">
                    {{ $title }}
                </span>
            </div>
        </div>
    </div>
</div>