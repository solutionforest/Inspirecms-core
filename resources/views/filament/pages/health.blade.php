<x-filament-panels::page>
    <x-filament::section>
        <ul>
            @foreach ($this->getStatusInfo() as $key => $item)
                @php
                    $title = $item['title'];
                    $isHealthy = $item['status']['isHealthy'] ?? null;
                    $icon = $isHealthy ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle';

                    $invalidStatus = ($item['status']['invalid'] ?? 0) . ' / ' . ($item['status']['total'] ?? 0);

                    $actionName = $item['action']?? null;
                @endphp
                <li class="inline-flex gap-2 items-center">
                    <strong>{{ $title }}</strong>
                    <span>Invalid: {{ $invalidStatus }}</span>
                    <x-filament::icon
                        icon="{{$icon}}"
                        class="h-5 w-5 text-custom-500 dark:text-custom-400"
                        @style(\Filament\Support\get_color_css_variables(
                            $isHealthy ? 'success' : 'danger',
                            shades: [400, 500],
                        ))
                    >
                    </x-filament::icon>

                    @if ($actionName)
                        {{ ($this->{$actionName})(['action' => $key]) }}
                    @endif
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-panels::page>
