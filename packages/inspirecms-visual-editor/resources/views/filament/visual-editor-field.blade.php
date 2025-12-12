<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            state: $wire.entangle('{{ $getStatePath() }}'),
            showEditor: false,
            layoutData: @js($getInitialData()),

            init() {
                if (this.state && typeof this.state === 'object') {
                    this.layoutData = this.state;
                }
            },

            openEditor() {
                this.showEditor = true;
            },

            closeEditor() {
                this.showEditor = false;
            },

            saveLayout(data) {
                this.layoutData = data;
                this.state = data;
                this.closeEditor();
            }
        }"
        class="ve-field-container"
    >
        {{-- Preview/Summary --}}
        <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
            {{-- Toolbar --}}
            <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-300 dark:border-gray-600">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-squares-2x2 class="w-5 h-5 text-gray-400" />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Visual Layout</span>
                    <span
                        x-show="layoutData && layoutData.root"
                        class="text-xs px-2 py-0.5 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full"
                    >
                        Has content
                    </span>
                </div>
                <button
                    type="button"
                    @click="openEditor()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
                >
                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                    Edit Layout
                </button>
            </div>

            {{-- Mini Preview --}}
            <div class="p-4 bg-white dark:bg-gray-900" style="min-height: 200px;">
                <template x-if="layoutData && layoutData.root">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex items-center gap-2 mb-2">
                            <x-heroicon-o-cube class="w-4 h-4" />
                            <span>Root: <span x-text="layoutData.root?.type || 'container'" class="font-medium"></span></span>
                        </div>
                        <div class="text-xs text-gray-400">
                            <span x-text="countBlocks(layoutData.root)"></span> blocks in layout
                        </div>
                    </div>
                </template>
                <template x-if="!layoutData || !layoutData.root">
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <x-heroicon-o-document-plus class="w-12 h-12 text-gray-300 mb-3" />
                        <p class="text-sm text-gray-500">No layout created yet</p>
                        <p class="text-xs text-gray-400 mt-1">Click "Edit Layout" to start building</p>
                    </div>
                </template>
            </div>
        </div>

        {{-- Full Editor Modal --}}
        <div
            x-show="showEditor"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-gray-900/80" @click="closeEditor()"></div>

            {{-- Editor Container --}}
            <div class="relative w-full h-full bg-white dark:bg-gray-900">
                {{-- Close button --}}
                <button
                    type="button"
                    @click="closeEditor()"
                    class="absolute top-4 right-4 z-10 p-2 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                >
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>

                {{-- Embedded Editor --}}
                <div class="h-full">
                    <livewire:visual-editor
                        :initial-data="$getInitialData()"
                        :layout-id="$getLayoutId()"
                        wire:key="visual-editor-{{ $getStatePath() }}"
                        @layout-saved="saveLayout($event.detail.layoutData)"
                    />
                </div>
            </div>
        </div>
    </div>

    <script>
        function countBlocks(block) {
            if (!block) return 0;
            let count = 1;
            if (block.children && Array.isArray(block.children)) {
                block.children.forEach(child => {
                    count += countBlocks(child);
                });
            }
            return count;
        }
    </script>
</x-dynamic-component>
