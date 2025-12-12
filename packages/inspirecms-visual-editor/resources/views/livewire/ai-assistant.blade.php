<div class="ve-ai-assistant h-full flex flex-col">
    {{-- Header --}}
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                <x-heroicon-o-sparkles class="w-5 h-5 text-white" />
            </div>
            <div>
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">AI Assistant</div>
                <div class="text-xs text-gray-500">Generate layouts with AI</div>
            </div>
        </div>
    </div>

    {{-- Mode Tabs --}}
    <div class="flex border-b border-gray-200 dark:border-gray-700">
        <button
            wire:click="$set('mode', 'generate')"
            class="flex-1 px-4 py-2 text-xs font-medium transition-colors"
            :class="$wire.mode === 'generate' ? 'text-purple-600 border-b-2 border-purple-600' : 'text-gray-500'"
        >
            Generate
        </button>
        <button
            wire:click="$set('mode', 'suggest')"
            class="flex-1 px-4 py-2 text-xs font-medium transition-colors"
            :class="$wire.mode === 'suggest' ? 'text-purple-600 border-b-2 border-purple-600' : 'text-gray-500'"
        >
            Suggest
        </button>
    </div>

    {{-- Content --}}
    <div class="flex-1 overflow-auto">
        {{-- Generate Mode --}}
        <div x-show="$wire.mode === 'generate'" class="p-4 space-y-4">
            {{-- Template Selection --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Template (optional)</label>
                <select
                    wire:model="selectedTemplate"
                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                >
                    <option value="">Choose a template...</option>
                    @foreach($templateOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Style Selection --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Style</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($styleOptions as $key => $label)
                        <button
                            type="button"
                            wire:click="$set('selectedStyle', '{{ $key }}')"
                            class="px-3 py-2 text-xs font-medium rounded-lg transition-colors"
                            :class="$wire.selectedStyle === '{{ $key }}'
                                ? 'bg-purple-100 text-purple-700 border-2 border-purple-300'
                                : 'bg-gray-100 text-gray-700 border-2 border-transparent hover:border-gray-300'"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Prompt --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Describe your layout</label>
                <textarea
                    wire:model="prompt"
                    rows="4"
                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                    placeholder="E.g., Create a landing page for a SaaS product with hero, features, pricing, and CTA sections..."
                ></textarea>
            </div>

            {{-- Quick Prompts --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Quick prompts</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(array_slice($this->quickPrompts, 0, 4) as $quickPrompt)
                        <button
                            type="button"
                            wire:click="useQuickPrompt('{{ $quickPrompt }}')"
                            class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors truncate max-w-full"
                        >
                            {{ \Illuminate\Support\Str::limit($quickPrompt, 40) }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Generate Button --}}
            <button
                type="button"
                wire:click="generateLayout"
                wire:loading.attr="disabled"
                class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-medium rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
                <span wire:loading.remove wire:target="generateLayout">
                    <x-heroicon-o-sparkles class="w-5 h-5" />
                </span>
                <span wire:loading wire:target="generateLayout">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
                <span wire:loading.remove wire:target="generateLayout">Generate Layout</span>
                <span wire:loading wire:target="generateLayout">Generating...</span>
            </button>
        </div>

        {{-- Suggest Mode --}}
        <div x-show="$wire.mode === 'suggest'" class="p-4 space-y-4">
            <button
                type="button"
                wire:click="suggestNext"
                wire:loading.attr="disabled"
                class="w-full py-3 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 font-medium rounded-lg hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors flex items-center justify-center gap-2"
            >
                <x-heroicon-o-light-bulb class="w-5 h-5" />
                <span>Suggest Next Blocks</span>
            </button>

            @if(count($suggestions) > 0)
                <div class="space-y-2">
                    <div class="text-xs font-medium text-gray-500">Suggestions</div>
                    @foreach($suggestions as $suggestion)
                        <button
                            type="button"
                            wire:click="applySuggestion({{ json_encode($suggestion) }})"
                            class="w-full p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-left"
                        >
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $suggestion['type'] ?? 'Block' }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $suggestion['reason'] ?? '' }}</div>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- History --}}
    @if(count($history) > 0)
        <div class="border-t border-gray-200 dark:border-gray-700">
            <button
                type="button"
                x-data="{ open: false }"
                @click="open = !open"
                class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
            >
                <span class="text-xs font-medium text-gray-500">Recent generations</span>
                <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400" x-bind:class="open ? 'rotate-180' : ''" />
            </button>
            <div x-show="open" x-collapse class="px-4 pb-3 space-y-2">
                @foreach($history as $item)
                    <button
                        type="button"
                        wire:click="useHistoryItem('{{ $item['id'] }}')"
                        class="w-full p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-left"
                    >
                        <div class="text-xs text-gray-700 dark:text-gray-300 truncate">{{ $item['prompt'] }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $item['created_at'] }}</div>
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</div>
