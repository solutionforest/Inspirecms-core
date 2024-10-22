@php

    $history = $record->contentVersions()
        ->with('publishLog')
        ->orderByDesc('created_at')
        ->paginate(10, pageName: 'history-page');

    $groupedHistory = tap($history, function ($paginatedInstance) {
        $items = $paginatedInstance->getCollection()->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        });

        $paginatedInstance->setCollection($items);
    });

    $columns = ['default' => 4];
    $dtColumnSpan = ['default' => 1];
    $ddColumnSpan = ['default' => 3];

@endphp
<div class="flex flex-col gap-y-2">
    <x-filament::grid
        default="4"
        class="gap-3"
    >
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

                            // @todo: rolback action with avoid_to_clean and publishLog logic
                        @endphp
                        <li class="font-mono text-sm border rounded-md shadow-sm px-1.5 py-1">
                            @if ($item->publishLog)
                                <div class="inline-flex py-1">
                                    <x-filament::badge size="sm" color="primary">
                                        {{ trans('inspirecms::resources/content.published_at.label') }}: {{ $item->publishLog->published_at?->format('Y-m-d H:i:s') }}
                                    </x-filament::badge>
                                </div>
                            @endif
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
    
    {{ $groupedHistory }}

</div>