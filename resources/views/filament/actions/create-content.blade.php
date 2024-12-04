@props(['documentTypes'])
<ul class="flex gap-2 flex-col">
    @if (count($documentTypes)> 0)
        @foreach ($documentTypes as $item)
            @php
                $label = $getLabelUsing($item) ?? null;
                $url = $getUrlUsing($item) ?? null;
                $icon = $item->icon ?? null;
            @endphp
            <li class="inline-flex w-full rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 hover:bg-gray-400/10 dark:hover:bg-white/5">
                <a href="{{ $url }}" class="flex gap-x-2 w-full p-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    @if ($icon)
                        <x-icon :name="$icon" class="w-5 h-5 text-gray-400 dark:text-gray-500" />
                    @endif
                    <span>
                        {{ $label }}
                    </span>
                </a>
            </li>
        @endforeach
    @else
        <p>
            {{ trans('inspirecms::actions.create_content.empty_state') }}
        </p>
    @endif
</ul>
