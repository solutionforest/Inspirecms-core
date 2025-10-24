@props(['items' => [], 'level' => 0, 'itemsCollapsible' => false, 'defaultCollapsedLevel' => 1])
<div {{ $attributes->class([
    'version-diff-items',
    'space-y-1' => $level === 0,
    'space-y-0.5' => $level === 1,
    'space-y-0' => $level > 1,
    'ml-4' => $level > 0,
    'pl-3' => $level > 0,
    'border-l-2' => $level > 0,
    'border-gray-200 dark:border-gray-600' => $level === 1,
    'border-gray-300 dark:border-gray-500' => $level === 2,
    'border-gray-400 dark:border-gray-400' => $level > 2,
]) }} 
    @if($level > 0) data-level="{{ $level }}" @endif
>
    @foreach ($items as $key => $value)
        <x-inspirecms::version-diff.item 
            :key="$key" 
            :value="$value" 
            :level="$level"
            :itemsCollapsible="$itemsCollapsible"
            :defaultCollapsedLevel="$defaultCollapsedLevel"
            @class([
                'version-diff-item-wrapper',
                'px-2 py-1.5 rounded bg-gray-50/30 dark:bg-gray-800/20' => $level === 0,
                'px-2 py-1 rounded-sm bg-gray-25 dark:bg-gray-800/10' => $level === 1,
                'px-1 py-0.5 rounded-sm bg-gray-50/20 dark:bg-gray-700/10' => $level > 1,
                'border-b border-gray-100 dark:border-gray-700 last:border-b-0' => $level === 0,
                'hover:bg-gray-100/50 dark:hover:bg-gray-700/30 transition-colors duration-150' => true,
            ]) />
    @endforeach
</div>