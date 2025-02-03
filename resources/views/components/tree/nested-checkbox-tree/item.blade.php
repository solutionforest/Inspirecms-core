@props([
    'key',
    'title',
    'description' => null,
    'groupKey' => null,
    'collapsable' => false,
    'isDisabled' => false,
])

<div class="py-0.5 flex items-center gap-x-0.5 w-full">
    <div class="grow px-1.5 rounded-md">
        <div 
            class="flex items-center gap-x-3"
            data-checkbox-treenode=@js($key)
            @if ($groupKey != null)
                data-checkbox-treenode-group=@js($groupKey)
            @endif
        >
            @if ($collapsable)
                <span x-on:click="collapse('{{ $key }}')" class="cursor-pointer">
                    <x-filament::icon x-show="!isCollapsed('{{ $key }}')" icon="heroicon-o-chevron-down" class="h-5 w-5 text-gray-500 dark:text-gray-100" />
                    <x-filament::icon x-show="isCollapsed('{{ $key }}')" icon="heroicon-o-chevron-up" class="h-5 w-5 text-gray-500 dark:text-gray-100" />
                </span>
            @endif
            <label class="flex items-center gap-1">
                <x-filament::input.checkbox
                    class="shrink-0 mt-0.5 border-gray-200 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800 indeterminate:bg-gray-300" 
                    value="{{ $key }}"
                    x-model="selected"
                    :disabled="$isDisabled"
                />
                <div class="ms-3">
                    <span>{{ $title }}</span>
                    @if ($description)
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
                    @endif
                </div>
            </label>
        </div>
    </div>
</div>