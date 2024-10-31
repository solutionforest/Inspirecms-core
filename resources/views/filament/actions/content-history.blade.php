@php

    $history = $record->contentVersions()
        ->with(['publishLog', 'author'])
        ->orderByDesc('created_at')
        ->paginate(10, pageName: 'history-page');

    $items = $history->getCollection()->transform(function ($item) {
        $diff = collect($item->getDifferences())
            ->map(fn ($diffsArr) => collect($diffsArr)
                ->map(fn ($value) => is_array($value) ? json_encode($value) : $value)
                ->all()
            )
            ->all();
        $data['diff'] = $diff;

        $publishTime = $item->publishLog?->published_at;
        $data['isPublished'] = $publishTime != null;
        $data['publishTime'] = $publishTime?->format('Y-m-d H:i:s');
        $data['publishTimeShort'] = $publishTime?->diffForHumans();

        $data['logTime'] = $item->created_at->format('Y-m-d H:i:s');

        $data['event'] = $item->event_name;
        $data['authorName'] = $item->author?->name;

        return $data;
    });

@endphp
<div class="flex flex-col gap-y-2">
    <div class="flow-root">
        
    <ul class="-mb-8">
        @foreach ($items as $item)
            @php
                $isPublished = boolval($item['isPublished'] ?? false);
            @endphp
            <li>
                <div class="relative pb-8">
                    <div class="relative flex space-x-3">
                        <div>
                            @unless ($loop->last)
                                <div class="absolute -bottom-6 left-0 top-6 flex w-6 justify-center">
                                    <div class="w-px bg-gray-200 dark:bg-gray-500"></div>
                                </div>
                            @endunless
                            <div class="relative flex h-6 w-6 flex-none items-center justify-center rounded-full bg-white dark:bg-white/5">
                                @if ($isPublished)
                                    <x-filament::icon
                                        icon="heroicon-o-eye"
                                        @style([
                                             \Filament\Support\get_color_css_variables(
                                                'success',
                                                shades: [400, 500],
                                            )
                                        ])
                                        class="w-5 w-5 text-custom-500 dark:text-custom-400"
                                    />
                                @else
                                    <div class="h-1.5 w-1.5 rounded-full bg-gray-100 ring-1 ring-gray-300"></div>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col gap-y-1 min-w-0 flex-1 pt-1.5">
                            <div class="inline-flex space-x-4 justify-between">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-white"><span class="font-medium text-gray-900 dark:text-gray-200">{{ $item['authorName'] }}</span> {{ $item['event']}}.</p>
                                </div>
                                <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-white/50">
                                    <time datetime="{{ $item['logTime'] }}">{{ $item['logTime'] }}</time>
                                </div>
                            </div>
                            @if ($isPublished)
                                <div class="flex-auto">
                                    <x-filament::badge color="primary" size="sm" class="w-full">
                                        <div class="inline-flex space-x-4 justify-between">
                                            <span>
                                                {{ @trans('inspirecms::inspirecms.publish_at_xxx', ['time' => $item['publishTime']]) }}
                                            </span>
                                            <time datetime="{{ $item['publishTime'] }}">{{ $item['publishTimeShort'] }}</time>
                                        </div>
                                    </x-filament::badge>
                                </div>
                            @endif
                            <div class="rounded-md p-3 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 shadow-xl">
                                @foreach ($item['diff'] ?? [] as $diffKey => $diffValue)
                                    <div class="flex justify-between gap-x-4">
                                        @php
                                            $from = $diffValue['from'] ?? '';
                                            $to = $diffValue['to'] ?? '';
                                        @endphp
                                        <span default="1">
                                            {{ $diffKey }}
                                        </span>
                                        <div default="2" class="text-xs/5">
                                            <span class="text-gray-400 line-through">{{ $from }}</span>
                                            <span>{{ $to }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>
    
    {{ $history }}

</div>