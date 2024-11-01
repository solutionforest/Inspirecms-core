@props(['documentTypes'])
<ul class="flex gap-2 flex-col">
    @foreach ($documentTypes as $item)
        @php
            $label = $getLabelUsing($item) ?? null;
            $url = $getUrlUsing($item) ?? null;
        @endphp
        <li class="inline-flex w-full rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 hover:bg-gray-400/10 dark:hover:bg-white/5">
            <a href="{{ $url }}" class="w-full p-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                {{ $label }}
            </a>
        </li>
    @endforeach
</ul>
