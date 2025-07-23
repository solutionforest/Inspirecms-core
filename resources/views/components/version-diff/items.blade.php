@props(['items' => []])
<div {{ $attributes->class(['version-diff-items']) }}>
    @foreach ($items as $key => $value)
        <x-inspirecms::version-diff.item 
            @class([
                'divide-x divide-gray-200 dark:divide-white/10',
                'divide-y first:divide-y-0',
            ])
            :key="$key" 
            :value="$value" />
    @endforeach
</div>