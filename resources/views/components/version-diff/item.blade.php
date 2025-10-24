@props(['key', 'value', 'level' => 0, 'itemsCollapsible' => false, 'defaultCollapsedLevel' => 1])

<div {{ $attributes->merge(['class' => 'version-diff-item']) }}>
    @if (is_array($value))
        {{-- Array/Object display --}}
        @php
            $isCollapsible = $itemsCollapsible && count($value) > 0;
            $shouldStartCollapsed = $isCollapsible && ($level >= $defaultCollapsedLevel);
        @endphp
        
        <div class="space-y-1" @if($isCollapsible) x-data="{ expanded: {{ $shouldStartCollapsed ? 'false' : 'true' }} }" data-collapsible-item="true" @endif>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    @if ($isCollapsible)
                        <button 
                            type="button"
                            @click="expanded = !expanded"
                            class="flex-shrink-0 p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                            :aria-expanded="expanded"
                            aria-label="Toggle section"
                        >
                            <svg 
                                class="w-3 h-3 text-gray-500 dark:text-gray-400 transition-transform duration-200"
                                :class="{ 'rotate-90': expanded }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    @endif
                    
                    <div @class([
                        'version-diff-item-key',
                        'font-semibold text-gray-800 dark:text-gray-200',
                        'text-sm' => $level > 0,
                        'text-base' => $level === 0,
                    ])>
                        {{ $key }}
                    </div>
                    <div @class([
                        'version-diff-item-type',
                        'text-xs px-2.5 py-1 rounded-full font-medium',
                        'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                    ])>
                        @if (count($value) === 0)
                            Empty {{ is_array($value) ? 'array' : 'object' }}
                        @else
                            {{ count($value) }} {{ count($value) === 1 ? 'item' : 'items' }}
                        @endif
                    </div>
                </div>
                
                @if (count($value) > 5)
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ count($value) }} entries
                    </div>
                @endif
            </div>
            
            @if (count($value) > 0)
                <div 
                    @class([
                        'version-diff-nested-content',
                    ])
                    @if($isCollapsible) 
                        :class="{ 'hidden': !expanded }"
                    @endif
                >
                    <x-inspirecms::version-diff.items 
                        :items="$value" 
                        :level="$level + 1"
                        :itemsCollapsible="$itemsCollapsible"
                        :defaultCollapsedLevel="$defaultCollapsedLevel"
                    />
                </div>
            @endif
        </div>
    @else
        {{-- Simple key-value display --}}
        <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-3">
            <div @class([
                'version-diff-item-key',
                'font-medium text-gray-700 dark:text-gray-300',
                'text-sm' => $level > 0,
                'text-base' => $level === 0,
                'flex-shrink-0',
                'min-w-0',
                'sm:w-1/3 lg:w-1/4',
            ])>
                <span class="break-words">{{ $key }}</span>
            </div>
            
            <div @class([
                'version-diff-item-value',
                'text-gray-900 dark:text-gray-100',
                'text-sm' => $level > 0,
                'text-base' => $level === 0,
                'flex-1',
                'min-w-0',
                // 'break-words',
                // 'whitespace-pre-wrap',
            ])>
                @if (is_string($value) && strlen($value) > 500)
                    <div x-data="{ expanded: false }" class="space-y-2">
                        <div x-show="!expanded">
                            <span>{{ substr($value, 0, 500) }}...</span>
                            <button 
                                @click="expanded = true"
                                class="ml-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm underline"
                            >
                                Show more
                            </button>
                        </div>
                        <div x-show="expanded" x-cloak>
                            <span>{!! $value !!}</span>
                            <button 
                                @click="expanded = false"
                                class="ml-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm underline"
                            >
                                Show less
                            </button>
                        </div>
                    </div>
                @elseif (is_null($value))
                    <span class="italic text-gray-400 dark:text-gray-500">null</span>
                @elseif (is_bool($value))
                    <span @class([
                        'font-medium',
                        'text-green-600 dark:text-green-400' => $value,
                        'text-red-600 dark:text-red-400' => !$value,
                    ])>
                        {{ $value ? 'true' : 'false' }}
                    </span>
                @elseif (is_numeric($value))
                    <span class="font-mono text-purple-600 dark:text-purple-400">{{ $value }}</span>
                @else
                    <span>{!! $value !!}</span>
                @endif
            </div>
        </div>
    @endif
</div>