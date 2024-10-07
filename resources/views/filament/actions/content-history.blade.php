@php
    $record->loadMissing(['contentVersions.publishLog']);
    $history = $record->contentVersions->sortByDesc('created_at');

    $groupedHistory = $history->groupBy(function ($item) {
        return $item->created_at->format('Y-m-d');
    });

    $columns = ['default' => 4];
    $dtColumnSpan = ['default' => 1];
    $ddColumnSpan = ['default' => 3];

@endphp
<x-filament::grid
    default="4"
    class="gap-3"
>

        <x-filament::grid.column 
            default="4"
            >
            <span class="text-danger-600">TODO remark: todo pagination. Remove this line after done</span>
        </x-filament::grid.column>
    @foreach ($groupedHistory as $date => $items)
        <x-filament::grid.column 
            default="1"
        >
            <x-filament::badge size="md">
                {{ $date }}
            </x-filament::badge>
        </x-filament::grid.column>
        
        <x-filament::grid.column 
            default="3"
        >
            <ul class="flex flex-col gap-2">
                @foreach ($items as $item)
                    @php
                        $data = $item->getDifferences();
                    @endphp
                    <li class="font-mono text-sm border rounded-md shadow-sm px-1.5 py-1">
                        <x-filament::grid default="3" class="gap-2">
                            @foreach ($data as $key => $diff)
                            @php
                                $from = $diff['from'] ?? '';
                                $to = $diff['to'] ?? '';
                                if (is_array($from)) {
                                    $from = json_encode($from);
                                }
                                if (is_array($to)) {
                                    $to = json_encode($to);
                                }
                            @endphp
                                <x-filament::grid.column default="1">
                                    {{ $key }}
                                </x-filament::grid.column>
                                <x-filament::grid.column default="2">
                                    <span class="text-gray-400" style="text-decoration-line: line-through;">{{ $from }}</span>
                                    <span>{{ $to }}</span>
                                </x-filament::grid.column>
                            @endforeach

                        </x-filament::grid>
                    </li>
                @endforeach
            </ul>
        </x-filament::grid.column>
    @endforeach
</x-filament::grid>