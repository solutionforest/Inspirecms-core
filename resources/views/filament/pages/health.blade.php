<x-filament-panels::page>
    @foreach ($this->getStatusInfo() as $key => $item)
        @php
            $title = $item['title'];
            $isHealthy = $item['status']['isHealthy'] ?? null;

            $invalidCount = ($item['status']['invalid'] ?? 0);

            $isInvalid = $invalidCount > 0;

            $icon = $isInvalid ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle';

            $actionName = $item['action']?? null;
        @endphp

        <div @class([
            'flex gap-x-1.5',
        ])>

            <x-filament::section 
                :collapsible="$isInvalid"
                :collapsed="$isInvalid"
                :icon="$isInvalid ? $icon : null"
                icon-size="md"
                icon-color="danger"
                class="flex-1"
            >
                @if ($isInvalid)
                    <x-slot name="heading">
                        {{ $title }}
                    </x-slot>

                    <x-slot name="headerEnd">
                        <span class="text-gray-400 dark:text-white">{{ $invalidCount }}</span>
                    </x-slot>
                @else
                <div class="flex items-center gap-3">
                    <x-filament::icon 
                        :icon="$icon" 
                        class="w-5 h-5 text-custom-500 dark:text-custom-400" 
                        @style( \Filament\Support\get_color_css_variables(
                            'success',
                            shades: [400, 500],
                        )) 
                    />
                    <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        {{ $title }}
                    </h3>
                </div>
                @endif
                    
                    
                @if ($isInvalid && count($item['data'] ?? []) > 0 && isset($item['data']['invalidMessage']))
                    <ul class="list-inside mx-2">
                        {{-- Only support one level of invalid message --}}
                        @foreach ($item['data']['invalidMessage'] as $messageKeyOrValue => $messageItem)
                            <li class="text-gray-500 dark:text-gray-200 select-none list-disc">
                                @if (is_array($messageItem))
                                    <span class="text-md">{{ $messageKeyOrValue }}</span>
                                    <ul class="list-inside ml-4">
                                        @foreach ($messageItem as $message)
                                            <li class="list-decimal">
                                                <span class="text-sm">{{ $message }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-sm">{{ $messageItem }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

            </x-filament::section>

            @if ($actionName && $isInvalid)
                {{ ($this->{$actionName})(['action' => $key]) }}
            @endif
        </div>
    @endforeach
</x-filament-panels::page>
