@php

    $items = $paginator->getCollection()->transform(function ($item) {
        $diff = collect($item->getDifferences())
            ->map(fn ($diffsArr, $k) => collect($diffsArr)
                ->map(fn ($value) => is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value)
                ->all()
            )
            ->all();

        $data['id'] = $item->getKey();

        $data['diff'] = $diff;

        $publishTime = $item?->publishLog?->published_at;
        $data['isPublished'] = $publishTime != null;
        $data['publishTime'] = $publishTime?->format('Y-m-d H:i:s');
        $data['publishTimeShort'] = $publishTime?->diffForHumans();

        $publishState = $item->publish_state;
        $publishStateOption = !blank($publishState) ? inspirecms_content_statuses()->getOption($publishState) : null;
        $data['publishState'] = $publishStateOption?->getLabel() ?? $publishState;
        $data['publishStateColor'] = $publishStateOption?->getColor() ?? 'gray';

        $data['logTime'] = $item->created_at?->format('Y-m-d H:i:s');

        $data['event'] = $item->event_name;
        $data['authorName'] = $item->author?->name;

        $data['avoidToClear'] = $item->avoid_to_clean;

        return $data;
    });

    $toggleAvoidToClearAction = $action->getModalAction('toggleAvoidToClear');
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
                            <div class="flex flex-col gap-y-2 min-w-0 flex-1 pt-1.5">
                                <div class="inline-flex gap-4 lg:items-center lg:justify-between flex-col lg:flex-row">
                                    <div class="inline-flex space-x-3">
                                        <p class="text-sm text-gray-500 dark:text-white"><span class="font-medium text-gray-900 dark:text-gray-200">{{ $item['authorName'] }}</span> {{ $item['event']}}.</p>
                                        <x-filament::badge :color="$item['publishStateColor']" size="xs" class="px-4">
                                            {{ $item['publishState'] }}
                                        </x-filament::badge>
                                    </div>
                                    <div class="inline-flex items-center gap-x-1.5">
                                        <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-white/50">
                                            <time datetime="{{ $item['logTime'] }}">{{ $item['logTime'] }}</time>
                                        </div>
                                        {{ $toggleAvoidToClearAction(['itemKey' => $item['id'], 'avoidToClear' => $item['avoidToClear'] ?? false ]) }}
                                    </div>
                                </div>
                                @if ($isPublished)
                                    <div class="flex-auto">
                                        <x-filament::badge color="primary" size="sm" class="w-full">
                                                <span class="text-sm">
                                                    {{ @trans('inspirecms::inspirecms.publish_at_xxx', ['time' => $item['publishTime']]) }}
                                                </span>
                                                <time class="font-light text-xs" datetime="{{ $item['publishTime'] }}">({{ $item['publishTimeShort'] }})</time>
                                        </x-filament::badge>
                                    </div>
                                @endif
                                <div class="rounded-md p-3 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 shadow-lg">
                                    @php
                                        $headingClasses = 'text-xs/5 font-mono font-thin text-gray-500 dark:text-gray-200';
                                        $dataColumnSpan = [
                                            'default' => 1,
                                            'md' => 2,
                                        ];
                                    @endphp
                                    <x-filament::grid default="3" md="5" class="gap-x-1">
                                        <x-filament::grid.column default="1">
                                            <span @class([$headingClasses])>{{ __('inspirecms::resources/content.history.field.label') }}</span>
                                        </x-filament::grid.column>
                                        <x-filament::grid.column :default="$dataColumnSpan['default']" :md="$dataColumnSpan['md']">
                                            <span @class([$headingClasses])>{{ __('inspirecms::resources/content.history.from.label') }}</span>
                                        </x-filament::grid.column>
                                        <x-filament::grid.column :default="$dataColumnSpan['default']" :md="$dataColumnSpan['md']">
                                            <span @class([$headingClasses])>{{ __('inspirecms::resources/content.history.to.label') }}</span>
                                        </x-filament::grid.column>
                                        @foreach ($item['diff'] ?? [] as $diffKey => $diffValue)
                                            <x-filament::grid.column>
                                                {{ $diffKey }}
                                            </x-filament::grid.column>
                                            <x-filament::grid.column class="text-gray-400 line-through text-xs/5" :default="$dataColumnSpan['default']" :md="$dataColumnSpan['md']">
                                                <pre class="overflow-auto h-full">{{ $diffValue['from'] ?? '' }}</pre>
                                            </x-filament::grid.column>
                                            <x-filament::grid.column class="text-xs/5" :default="$dataColumnSpan['default']" :md="$dataColumnSpan['md']">
                                                <pre class="overflow-auto h-full">{{ $diffValue['to'] ?? '' }}</pre>
                                            </x-filament::grid.column>
                                        @endforeach
                                    </x-filament::grid>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
    
    <x-inspirecms::pagination.modal-pagination
        :paginator="$paginator"
        :page-options="$action->getPageOptions()"
        :per-page="$perPage"
        extreme-links
    />
</div>