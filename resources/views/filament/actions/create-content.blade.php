@props(['documentTypes'])
<ul class="flex gap-2 flex-col">
    @foreach ($documentTypes as $item)
        @php
            $label = $getLabelUsing($item) ?? null;
            $documentTypeKey = $item->getKey();

            $extraActionName = 'selectDocumentType';
            $extraActionArgs = "{documentTypeKey:'{$documentTypeKey}'}";
            $livewireAction = match ($actionType) {
                'action' => "mountAction('{$extraActionName}', {$extraActionArgs})",
                'table-action' => "mountTableAction('{$extraActionName}', null, {$extraActionArgs})",
                default => null,
            }
        @endphp
        <li class="inline-flex rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 hover:bg-gray-400/10 dark:hover:bg-white/5">
        <button class="w-full p-3 truncate text-left"
            @if ($livewireAction)
                wire:click="{{ $livewireAction }}"
            @endif
        >
            {{ $label }}
        </button>
        </li>
    @endforeach
</ul>
