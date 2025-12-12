<div
    x-data="visualEditor({
        layoutData: @js($this->layoutData),
        selectedBlockId: @entangle('selectedBlockId'),
        hoveredBlockId: @entangle('hoveredBlockId'),
        previewMode: @entangle('previewMode'),
    })"
    x-on:keydown.mod.s.prevent="$wire.save()"
    x-on:keydown.mod.z.prevent="$wire.undo()"
    x-on:keydown.mod.shift.z.prevent="$wire.redo()"
    x-on:keydown.delete="deleteSelectedBlock()"
    x-on:keydown.mod.d.prevent="duplicateSelectedBlock()"
    x-on:keydown.mod.c.prevent="$wire.copyBlock()"
    x-on:keydown.mod.v.prevent="$wire.pasteBlock()"
    class="ve-editor-container flex h-screen bg-gray-100 dark:bg-gray-900"
>
    {{-- Left Sidebar - Layers & Blocks --}}
    <div class="ve-left-sidebar w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col" x-show="showLayers || showBlocks">
        {{-- Sidebar Tabs --}}
        <div class="flex border-b border-gray-200 dark:border-gray-700">
            <button
                type="button"
                @click="$wire.showLayers = true; $wire.showBlocks = false"
                class="flex-1 px-4 py-3 text-sm font-medium flex items-center justify-center gap-2 transition-colors"
                :class="$wire.showLayers ? 'text-primary-600 border-b-2 border-primary-600' : 'text-gray-500 hover:text-gray-700'"
            >
                <x-heroicon-o-bars-3-bottom-left class="w-4 h-4" />
                Layers
            </button>
            <button
                type="button"
                @click="$wire.showBlocks = true; $wire.showLayers = false"
                class="flex-1 px-4 py-3 text-sm font-medium flex items-center justify-center gap-2 transition-colors"
                :class="$wire.showBlocks ? 'text-primary-600 border-b-2 border-primary-600' : 'text-gray-500 hover:text-gray-700'"
            >
                <x-heroicon-o-squares-plus class="w-4 h-4" />
                Blocks
            </button>
        </div>

        {{-- Layers Panel --}}
        <div x-show="$wire.showLayers" class="flex-1 overflow-auto">
            <livewire:inspirecms-visual-editor-layers-panel :block-tree="$this->blockTree" />
        </div>

        {{-- Blocks Panel --}}
        <div x-show="$wire.showBlocks" class="flex-1 overflow-auto">
            <livewire:inspirecms-visual-editor-block-panel />
        </div>
    </div>

    {{-- Main Canvas Area --}}
    <div class="ve-main-canvas flex-1 flex flex-col">
        {{-- Toolbar --}}
        <div class="ve-toolbar h-14 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-4">
            {{-- Left: Toggle Buttons --}}
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="$wire.togglePanel('layers')"
                    class="p-2 rounded-lg transition-colors"
                    :class="$wire.showLayers ? 'bg-primary-50 text-primary-600' : 'text-gray-500 hover:bg-gray-100'"
                    title="Toggle Layers"
                >
                    <x-heroicon-o-bars-3-bottom-left class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="$wire.togglePanel('blocks')"
                    class="p-2 rounded-lg transition-colors"
                    :class="$wire.showBlocks ? 'bg-primary-50 text-primary-600' : 'text-gray-500 hover:bg-gray-100'"
                    title="Toggle Blocks"
                >
                    <x-heroicon-o-squares-plus class="w-5 h-5" />
                </button>

                <div class="w-px h-6 bg-gray-200 dark:bg-gray-700 mx-2"></div>

                {{-- Undo/Redo --}}
                <button
                    type="button"
                    wire:click="undo"
                    class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="!$wire.canUndo()"
                    title="Undo (Ctrl+Z)"
                >
                    <x-heroicon-o-arrow-uturn-left class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    wire:click="redo"
                    class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="!$wire.canRedo()"
                    title="Redo (Ctrl+Shift+Z)"
                >
                    <x-heroicon-o-arrow-uturn-right class="w-5 h-5" />
                </button>
            </div>

            {{-- Center: Preview Mode --}}
            <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                <button
                    type="button"
                    @click="$wire.setPreviewMode('desktop')"
                    class="p-2 rounded-md transition-colors"
                    :class="previewMode === 'desktop' ? 'bg-white dark:bg-gray-600 shadow-sm' : 'text-gray-500'"
                    title="Desktop Preview"
                >
                    <x-heroicon-o-computer-desktop class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="$wire.setPreviewMode('tablet')"
                    class="p-2 rounded-md transition-colors"
                    :class="previewMode === 'tablet' ? 'bg-white dark:bg-gray-600 shadow-sm' : 'text-gray-500'"
                    title="Tablet Preview"
                >
                    <x-heroicon-o-device-tablet class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="$wire.setPreviewMode('mobile')"
                    class="p-2 rounded-md transition-colors"
                    :class="previewMode === 'mobile' ? 'bg-white dark:bg-gray-600 shadow-sm' : 'text-gray-500'"
                    title="Mobile Preview"
                >
                    <x-heroicon-o-device-phone-mobile class="w-5 h-5" />
                </button>
            </div>

            {{-- Right: Actions --}}
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="$wire.togglePanel('ai')"
                    class="p-2 rounded-lg transition-colors"
                    :class="$wire.showAI ? 'bg-purple-50 text-purple-600' : 'text-gray-500 hover:bg-gray-100'"
                    title="AI Assistant"
                >
                    <x-heroicon-o-sparkles class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="$wire.togglePanel('settings')"
                    class="p-2 rounded-lg transition-colors"
                    :class="$wire.showSettings ? 'bg-primary-50 text-primary-600' : 'text-gray-500 hover:bg-gray-100'"
                    title="Toggle Settings"
                >
                    <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
                </button>

                <div class="w-px h-6 bg-gray-200 dark:bg-gray-700 mx-2"></div>

                <button
                    type="button"
                    wire:click="save"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
                >
                    <x-heroicon-o-cloud-arrow-up class="w-5 h-5" />
                    <span>Save</span>
                    <span x-show="$wire.isDirty" class="w-2 h-2 bg-white rounded-full"></span>
                </button>
            </div>
        </div>

        {{-- Canvas --}}
        <div class="ve-canvas-wrapper flex-1 overflow-auto p-8 bg-gray-100 dark:bg-gray-900">
            <div
                class="ve-canvas mx-auto bg-white shadow-lg rounded-lg overflow-hidden transition-all duration-300"
                :class="{
                    'w-full max-w-full': previewMode === 'desktop',
                    'w-[768px]': previewMode === 'tablet',
                    'w-[375px]': previewMode === 'mobile'
                }"
                x-ref="canvas"
            >
                {{-- Rendered Preview --}}
                <div
                    class="ve-preview-content min-h-[600px]"
                    x-html="renderedPreview"
                    @click="handleCanvasClick($event)"
                    @mouseover="handleCanvasHover($event)"
                    @mouseout="handleCanvasHoverOut($event)"
                ></div>
            </div>
        </div>
    </div>

    {{-- Right Sidebar - Settings & AI --}}
    <div class="ve-right-sidebar w-80 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 flex flex-col" x-show="showSettings || showAI">
        {{-- Settings Panel --}}
        <div x-show="$wire.showSettings && !$wire.showAI" class="flex-1 overflow-auto">
            <livewire:inspirecms-visual-editor-settings-panel />
        </div>

        {{-- AI Panel --}}
        <div x-show="$wire.showAI" class="flex-1 overflow-auto">
            <livewire:inspirecms-visual-editor-ai-assistant />
        </div>
    </div>

    {{-- Block Context Menu --}}
    <div
        x-show="contextMenu.show"
        x-transition
        @click.away="contextMenu.show = false"
        class="ve-context-menu fixed bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-1 z-50 min-w-[180px]"
        :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }"
    >
        <button type="button" @click="addBlockBefore()" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
            <x-heroicon-o-arrow-up-on-square class="w-4 h-4" />
            Add Block Before
        </button>
        <button type="button" @click="addBlockAfter()" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
            <x-heroicon-o-arrow-down-on-square class="w-4 h-4" />
            Add Block After
        </button>
        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
        <button type="button" @click="duplicateSelectedBlock()" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
            <x-heroicon-o-document-duplicate class="w-4 h-4" />
            Duplicate
        </button>
        <button type="button" @click="$wire.copyBlock()" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
            <x-heroicon-o-clipboard-document class="w-4 h-4" />
            Copy
        </button>
        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
        <button type="button" @click="deleteSelectedBlock()" class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
            <x-heroicon-o-trash class="w-4 h-4" />
            Delete
        </button>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('visualEditor', (config) => ({
        layoutData: config.layoutData,
        selectedBlockId: config.selectedBlockId,
        hoveredBlockId: config.hoveredBlockId,
        previewMode: config.previewMode,
        contextMenu: { show: false, x: 0, y: 0, blockId: null },
        renderedPreview: '',
        showLayers: true,
        showBlocks: false,
        showSettings: true,
        showAI: false,

        init() {
            this.renderPreview();

            this.$watch('layoutData', () => {
                this.renderPreview();
            });

            // Listen for layout changes
            Livewire.on('layout-changed', () => {
                this.renderPreview();
            });

            Livewire.on('block-added', ({ blockId }) => {
                this.renderPreview();
                this.$nextTick(() => this.highlightBlock(blockId));
            });

            Livewire.on('block-updated', ({ blockId }) => {
                this.renderPreview();
            });

            Livewire.on('block-deleted', ({ blockId }) => {
                this.renderPreview();
            });
        },

        renderPreview() {
            // Render the layout to HTML
            this.renderedPreview = this.renderBlock(this.layoutData.root);
        },

        renderBlock(block) {
            if (!block) return '';

            const isSelected = this.selectedBlockId === block.id;
            const isHovered = this.hoveredBlockId === block.id;

            let classes = ['ve-block'];
            if (isSelected) classes.push('ve-block-selected');
            if (isHovered) classes.push('ve-block-hovered');

            // Render children
            let childrenHtml = '';
            if (block.children && block.children.length > 0) {
                childrenHtml = block.children.map(child => this.renderBlock(child)).join('');
            }

            // Build style
            let style = this.buildBlockStyle(block);

            return `<div class="${classes.join(' ')}" data-block-id="${block.id}" data-block-type="${block.type}" style="${style}">${childrenHtml || this.getPlaceholder(block)}</div>`;
        },

        buildBlockStyle(block) {
            const props = block.props || {};
            const styles = block.styles || {};
            let css = [];

            // Container/layout styles
            if (['container', 'section', 'column'].includes(block.type)) {
                css.push('display: flex');
                css.push(`flex-direction: ${props.flexDirection || 'column'}`);
                css.push(`justify-content: ${props.justifyContent || 'flex-start'}`);
                css.push(`align-items: ${props.alignItems || 'stretch'}`);
                if (props.gap) css.push(`gap: ${props.gap}`);
            }

            if (block.type === 'grid') {
                css.push('display: grid');
                const cols = props.columns || 2;
                css.push(`grid-template-columns: repeat(${cols}, 1fr)`);
                if (props.gap) css.push(`gap: ${props.gap}`);
            }

            // Size
            if (props.maxWidth) css.push(`max-width: ${props.maxWidth}`);
            if (props.minHeight) css.push(`min-height: ${props.minHeight}`);

            // Padding
            if (props.paddingY) {
                css.push(`padding-top: ${props.paddingY}`);
                css.push(`padding-bottom: ${props.paddingY}`);
            }
            if (props.paddingX) {
                css.push(`padding-left: ${props.paddingX}`);
                css.push(`padding-right: ${props.paddingX}`);
            }

            // Background
            if (styles.backgroundColor) css.push(`background-color: ${styles.backgroundColor}`);

            return css.join('; ');
        },

        getPlaceholder(block) {
            if (block.type === 'heading') {
                const level = block.props?.level || 2;
                return `<h${level} style="text-align: ${block.props?.alignment || 'left'}">${block.props?.text || 'Heading'}</h${level}>`;
            }
            if (block.type === 'text') {
                return `<div style="text-align: ${block.props?.alignment || 'left'}">${block.props?.content || 'Text content'}</div>`;
            }
            if (block.type === 'button') {
                return `<button style="padding: 12px 24px; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer;">${block.props?.text || 'Button'}</button>`;
            }
            if (block.type === 'image') {
                const src = block.props?.src || '';
                if (src) {
                    return `<img src="${src}" alt="${block.props?.alt || ''}" style="max-width: 100%; height: auto;" />`;
                }
                return `<div style="background: #e5e7eb; padding: 48px; text-align: center; color: #9ca3af;">Image Placeholder</div>`;
            }
            if (block.type === 'spacer') {
                return `<div style="height: ${block.props?.height || '48px'}; border: 1px dashed #e5e7eb;"></div>`;
            }
            if (block.type === 'divider') {
                return `<hr style="border: none; border-top: 1px solid ${block.props?.color || '#e5e7eb'}; margin: ${block.props?.marginY || '24px'} 0;" />`;
            }

            // Container placeholder
            return `<div style="padding: 24px; border: 2px dashed #e5e7eb; border-radius: 8px; text-align: center; color: #9ca3af;">Drop blocks here</div>`;
        },

        handleCanvasClick(event) {
            const blockEl = event.target.closest('[data-block-id]');
            if (blockEl) {
                const blockId = blockEl.dataset.blockId;
                this.selectedBlockId = blockId;
                this.$wire.selectBlock(blockId);

                // Send block data to settings panel
                const block = this.findBlock(blockId, this.layoutData.root);
                if (block) {
                    this.$dispatch('block-selected', { blockId, blockData: block });
                }
            }
        },

        handleCanvasHover(event) {
            const blockEl = event.target.closest('[data-block-id]');
            if (blockEl) {
                this.hoveredBlockId = blockEl.dataset.blockId;
            }
        },

        handleCanvasHoverOut(event) {
            this.hoveredBlockId = null;
        },

        showContextMenu(event, blockId) {
            event.preventDefault();
            this.contextMenu = {
                show: true,
                x: event.clientX,
                y: event.clientY,
                blockId: blockId
            };
        },

        findBlock(blockId, block) {
            if (!block) return null;
            if (block.id === blockId) return block;
            for (const child of (block.children || [])) {
                const found = this.findBlock(blockId, child);
                if (found) return found;
            }
            return null;
        },

        highlightBlock(blockId) {
            const el = this.$refs.canvas?.querySelector(`[data-block-id="${blockId}"]`);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },

        deleteSelectedBlock() {
            if (this.selectedBlockId) {
                this.$wire.deleteBlock(this.selectedBlockId);
            }
        },

        duplicateSelectedBlock() {
            if (this.selectedBlockId) {
                this.$wire.duplicateBlock(this.selectedBlockId);
            }
            this.contextMenu.show = false;
        },

        addBlockBefore() {
            // Implementation for adding block before
            this.contextMenu.show = false;
        },

        addBlockAfter() {
            // Implementation for adding block after
            this.contextMenu.show = false;
        }
    }));
});
</script>
@endpush

@push('styles')
<style>
.ve-block {
    position: relative;
    min-height: 20px;
    transition: outline 0.15s ease;
}

.ve-block:hover {
    outline: 2px solid rgba(59, 130, 246, 0.3);
    outline-offset: 2px;
}

.ve-block-selected {
    outline: 2px solid #3b82f6 !important;
    outline-offset: 2px;
}

.ve-block-hovered {
    outline: 2px dashed #3b82f6;
    outline-offset: 2px;
}

.ve-canvas-wrapper {
    background-image: radial-gradient(circle, #e5e7eb 1px, transparent 1px);
    background-size: 20px 20px;
}
</style>
@endpush
