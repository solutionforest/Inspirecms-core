<div class="ve-settings-panel h-full flex flex-col">
    @if($blockId && $blockType)
        {{-- Block Header --}}
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @php
                        $blockInstance = \SolutionForest\InspireCms\VisualEditor\Blocks\Registry\BlockRegistry::get($blockType);
                    @endphp
                    <div class="w-10 h-10 rounded-lg bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center">
                        @if($blockInstance)
                            <x-dynamic-component :component="$blockInstance->getIcon()" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                        @else
                            <x-heroicon-o-cube class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                        @endif
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $blockInstance?->getLabel() ?? ucfirst($blockType) }}
                        </div>
                        <div class="text-xs text-gray-500">{{ $blockType }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        wire:click="duplicateBlock"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                        title="Duplicate"
                    >
                        <x-heroicon-o-document-duplicate class="w-4 h-4" />
                    </button>
                    <button
                        type="button"
                        wire:click="deleteBlock"
                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                        title="Delete"
                    >
                        <x-heroicon-o-trash class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        {{-- Settings Form --}}
        <div class="flex-1 overflow-auto p-4">
            <form wire:submit.prevent>
                {{ $this->form }}
            </form>
        </div>

        {{-- Quick Actions --}}
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
            <div class="flex gap-2">
                <button
                    type="button"
                    wire:click="saveBlock"
                    class="flex-1 px-3 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
                >
                    Apply Changes
                </button>
            </div>
        </div>
    @else
        {{-- No Selection --}}
        <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                <x-heroicon-o-cursor-arrow-rays class="w-8 h-8 text-gray-400" />
            </div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No Block Selected</h3>
            <p class="text-xs text-gray-500">Click on a block in the canvas to edit its properties</p>
        </div>
    @endif
</div>
