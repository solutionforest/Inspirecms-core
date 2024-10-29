<x-filament-panels::page>
    @foreach ($this->getStatusInfo() as $key => $item)
        @php
            $title = $item['title'];
            $isHealthy = $item['status']['isHealthy'] ?? null;
            $icon = $isHealthy ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle';

            $invalidCount = ($item['status']['invalid'] ?? 0);

            $actionName = $item['action']?? null;
        @endphp
        <x-filament::section 
            collapsible
            collapsed
            :icon="$icon"
            icon-size="md"
            icon-color="{{ $isHealthy ? 'success' : 'danger' }}"
        >
            <x-slot name="heading">
                {{ $title }}
            </x-slot>
 
            <x-slot name="headerEnd">
                @if (!$isHealthy)
                    <span class="text-gray-400 dark:text-white">{{ $invalidCount }}</span>
                @endif
            </x-slot>

            @switch($key)
                @case('permissions')
                    @php
                        $invalidPermissions = collect($item['data'] ?? [])->where(fn ($arr) => $arr['valid'] === false)->all();
                    @endphp
                    @if (count($invalidPermissions))
                        <div class="flex gap-2 flex-col md:flex-col-reverse">
                            <ul class="flex-1">
                                @foreach ($invalidPermissions as $permissionData)
                                    <li class="text-sm text-gray-500 dark:text-gray-200 select-none">
                                        <span>{{ $permissionData['name'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($actionName)
                                <div>
                                    {{ ($this->{$actionName})(['action' => $key]) }}
                                </div>
                            @endif
                        </div>
                    @endif
                    @break
                @default
                    
            @endswitch

        </x-filament::section>
    @endforeach
</x-filament-panels::page>
