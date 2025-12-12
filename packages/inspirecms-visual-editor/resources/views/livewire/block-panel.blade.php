<div class="ve-block-panel h-full flex flex-col">
    {{-- Search --}}
    <div class="p-3 border-b border-gray-200 dark:border-gray-700">
        <div class="relative">
            <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search blocks..."
                class="w-full pl-10 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-500"
            />
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex border-b border-gray-200 dark:border-gray-700">
        <button
            wire:click="$set('tab', 'blocks')"
            class="flex-1 px-3 py-2 text-xs font-medium transition-colors"
            :class="$wire.tab === 'blocks' ? 'text-primary-600 border-b-2 border-primary-600' : 'text-gray-500'"
        >
            Blocks
        </button>
        <button
            wire:click="$set('tab', 'templates')"
            class="flex-1 px-3 py-2 text-xs font-medium transition-colors"
            :class="$wire.tab === 'templates' ? 'text-primary-600 border-b-2 border-primary-600' : 'text-gray-500'"
        >
            Templates
        </button>
        <button
            wire:click="$set('tab', 'saved')"
            class="flex-1 px-3 py-2 text-xs font-medium transition-colors"
            :class="$wire.tab === 'saved' ? 'text-primary-600 border-b-2 border-primary-600' : 'text-gray-500'"
        >
            Saved
        </button>
    </div>

    {{-- Block Categories --}}
    <div class="flex-1 overflow-auto" x-show="$wire.tab === 'blocks'">
        @foreach($this->filteredCategories as $category)
            <div class="border-b border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    wire:click="setActiveCategory('{{ $category['key'] }}')"
                    class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                >
                    <div class="flex items-center gap-2">
                        <x-dynamic-component :component="$category['icon']" class="w-4 h-4 text-gray-500" />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $category['label'] }}</span>
                        <span class="text-xs text-gray-400">({{ count($category['blocks']) }})</span>
                    </div>
                    <x-heroicon-o-chevron-down
                        class="w-4 h-4 text-gray-400 transition-transform"
                        x-bind:class="$wire.activeCategory === '{{ $category['key'] }}' ? 'rotate-180' : ''"
                    />
                </button>

                <div
                    x-show="$wire.activeCategory === '{{ $category['key'] }}'"
                    x-collapse
                    class="px-3 pb-3"
                >
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($category['blocks'] as $block)
                            <button
                                type="button"
                                wire:click="addBlock('{{ $block['type'] }}')"
                                class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-left group"
                                draggable="true"
                                x-on:dragstart="$event.dataTransfer.setData('block-type', '{{ $block['type'] }}')"
                            >
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-10 h-10 rounded-lg bg-white dark:bg-gray-600 flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
                                        <x-dynamic-component :component="$block['icon']" class="w-5 h-5 text-gray-600 dark:text-gray-300" />
                                    </div>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">{{ $block['label'] }}</span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Templates Tab --}}
    <div class="flex-1 overflow-auto p-3" x-show="$wire.tab === 'templates'">
        <div class="space-y-3">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Pre-built Sections</div>
            @foreach(['Hero Section', 'Features Grid', 'Testimonials', 'Pricing Table', 'Team Section', 'FAQ Section', 'CTA Banner', 'Contact Form'] as $template)
                <button
                    type="button"
                    class="w-full p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-left"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center">
                            <x-heroicon-o-squares-2x2 class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $template }}</div>
                            <div class="text-xs text-gray-500">Click to add</div>
                        </div>
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Saved Tab --}}
    <div class="flex-1 overflow-auto p-3" x-show="$wire.tab === 'saved'">
        @if(count($this->savedTemplates) > 0)
            <div class="space-y-2">
                @foreach($this->savedTemplates as $template)
                    <button
                        type="button"
                        wire:click="addTemplate('{{ $template['id'] }}')"
                        class="w-full p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-left"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                <x-heroicon-o-document class="w-5 h-5 text-gray-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">{{ $template['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $template['type'] }}</div>
                            </div>
                            @if($template['isGlobal'])
                                <span class="text-xs px-2 py-0.5 bg-purple-100 text-purple-600 rounded-full">Global</span>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <x-heroicon-o-bookmark class="w-12 h-12 text-gray-300 mb-3" />
                <p class="text-sm text-gray-500">No saved blocks yet</p>
                <p class="text-xs text-gray-400 mt-1">Save blocks to reuse them across pages</p>
            </div>
        @endif
    </div>
</div>
