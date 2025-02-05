@php
    $isDisabled = $isDisabled();

    $addAction = $getAction('add');
    $editAction = $getAction('edit');
    $deleteAction = $getAction('delete');
@endphp
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @foreach ($getFormattedStateForDisplay() as $item)
        @php
            $itemKey = $item['key'];
        @endphp
        <div class="flex items-center gap-2">
            <div class="flex-1">
                <p>{{ $item['title'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ implode(', ', $item['permissions']) }}</p>
            </div>
            @if (! $isDisabled)
                <div class="flex gap-2">
                    {{ $editAction(['itemKey' => $itemKey]) }}
                    {{ $deleteAction(['itemKey' => $itemKey]) }}
                </div>
            @endif
        </div>
    @endforeach
    @if (! $isDisabled)
        {{ $addAction }}
    @endif
</x-dynamic-component>