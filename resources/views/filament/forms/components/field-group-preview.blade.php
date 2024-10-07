@props(['fieldGroupFieldsStatePath'])
<div x-data="{ state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }} }">
    <template x-for="(item) in state">
        <div class="dark:ring-white/20 gap-1.5 grid grid-cols-3 lg:grid-cols-4 items-center mb-4 ring-1 ring-gray-900/10 rounded-md shadow-sm">
            <span class="p-4 bg-gray-200 dark:!bg-gray-700 rounded-l-md" x-text="item.type">
            </span>
            <span class="p-4 col-span-2 lg:col-span-3 truncate" x-text="item.label">
            </span>
        </div>
    </template>
</div>