/**
 * Visual Editor - Alpine.js Components
 *
 * This file contains all Alpine.js components for the visual editor:
 * - visualEditorCanvas: Main canvas with drag-drop support
 * - visualEditorBlockPanel: Block selection panel
 * - visualEditorLayersTree: Hierarchical layer navigation
 * - visualEditorContextMenu: Right-click context menu
 */

document.addEventListener('alpine:init', () => {
    /**
     * Main Visual Editor Canvas Component
     */
    Alpine.data('visualEditorCanvas', (initialData = {}) => ({
        // State
        blocks: initialData.blocks || [],
        selectedBlockId: null,
        hoveredBlockId: null,
        viewport: 'desktop', // 'desktop', 'tablet', 'mobile'
        zoom: 100,
        isDragging: false,
        dragSource: null,
        dragType: null, // 'new' or 'move'
        dropTarget: null,
        dropPosition: null, // 'before', 'after', 'inside'

        // Clipboard
        clipboard: null,

        // Context menu
        contextMenu: {
            show: false,
            x: 0,
            y: 0,
            blockId: null,
        },

        init() {
            // Listen for Livewire events
            this.$wire?.on('blockAdded', (data) => this.onBlockAdded(data));
            this.$wire?.on('blockUpdated', (data) => this.onBlockUpdated(data));
            this.$wire?.on('blockRemoved', (data) => this.onBlockRemoved(data));
            this.$wire?.on('layoutLoaded', (data) => this.onLayoutLoaded(data));

            // Global click handler to close context menu
            document.addEventListener('click', () => this.closeContextMenu());

            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => this.handleKeyboard(e));
        },

        // Block selection
        selectBlock(blockId) {
            this.selectedBlockId = blockId;
            this.$wire?.call('selectBlock', blockId);
        },

        deselectBlock() {
            this.selectedBlockId = null;
            this.$wire?.call('selectBlock', null);
        },

        isSelected(blockId) {
            return this.selectedBlockId === blockId;
        },

        // Viewport
        setViewport(viewport) {
            this.viewport = viewport;
        },

        getCanvasClass() {
            return {
                'desktop': 've-canvas',
                'tablet': 've-canvas ve-canvas--tablet',
                'mobile': 've-canvas ve-canvas--mobile',
            }[this.viewport] || 've-canvas';
        },

        // Drag and Drop - Start drag from block panel (new block)
        startDragNewBlock(e, blockType) {
            this.isDragging = true;
            this.dragType = 'new';
            this.dragSource = { type: blockType };

            // Set drag data
            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData('text/plain', JSON.stringify({
                type: 'new',
                blockType: blockType,
            }));

            // Create drag ghost
            const ghost = this.createDragGhost(blockType);
            e.dataTransfer.setDragImage(ghost, 0, 0);
            setTimeout(() => ghost.remove(), 0);
        },

        // Drag and Drop - Start drag from canvas (move block)
        startDragBlock(e, blockId) {
            this.isDragging = true;
            this.dragType = 'move';
            this.dragSource = { blockId: blockId };

            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', JSON.stringify({
                type: 'move',
                blockId: blockId,
            }));

            // Add dragging class
            e.target.classList.add('ve-canvas-block--dragging');
        },

        // Handle drag over
        handleDragOver(e, targetBlockId, position) {
            e.preventDefault();
            e.dataTransfer.dropEffect = this.dragType === 'new' ? 'copy' : 'move';

            this.dropTarget = targetBlockId;
            this.dropPosition = position;
        },

        // Handle drag leave
        handleDragLeave(e) {
            // Only clear if leaving the drop zone entirely
            if (!e.currentTarget.contains(e.relatedTarget)) {
                this.dropTarget = null;
                this.dropPosition = null;
            }
        },

        // Handle drop
        handleDrop(e, targetBlockId, position) {
            e.preventDefault();

            try {
                const data = JSON.parse(e.dataTransfer.getData('text/plain'));

                if (data.type === 'new') {
                    this.$wire?.call('addBlock', data.blockType, targetBlockId, position);
                } else if (data.type === 'move') {
                    this.$wire?.call('moveBlock', data.blockId, targetBlockId, position);
                }
            } catch (err) {
                console.error('Drop error:', err);
            }

            this.endDrag();
        },

        // Handle drop on empty canvas
        handleDropOnCanvas(e) {
            e.preventDefault();

            try {
                const data = JSON.parse(e.dataTransfer.getData('text/plain'));

                if (data.type === 'new') {
                    this.$wire?.call('addBlock', data.blockType, null, 'inside');
                }
            } catch (err) {
                console.error('Drop error:', err);
            }

            this.endDrag();
        },

        // End drag operation
        endDrag() {
            this.isDragging = false;
            this.dragType = null;
            this.dragSource = null;
            this.dropTarget = null;
            this.dropPosition = null;

            // Remove dragging classes
            document.querySelectorAll('.ve-canvas-block--dragging').forEach(el => {
                el.classList.remove('ve-canvas-block--dragging');
            });
        },

        // Create drag ghost element
        createDragGhost(label) {
            const ghost = document.createElement('div');
            ghost.className = 've-drag-ghost';
            ghost.textContent = label;
            ghost.style.position = 'absolute';
            ghost.style.top = '-1000px';
            document.body.appendChild(ghost);
            return ghost;
        },

        // Check if block is drop target
        isDropTarget(blockId, position) {
            return this.dropTarget === blockId && this.dropPosition === position;
        },

        // Context menu
        showContextMenu(e, blockId) {
            e.preventDefault();
            this.selectBlock(blockId);
            this.contextMenu = {
                show: true,
                x: e.clientX,
                y: e.clientY,
                blockId: blockId,
            };
        },

        closeContextMenu() {
            this.contextMenu.show = false;
        },

        // Context menu actions
        duplicateBlock(blockId) {
            this.$wire?.call('duplicateBlock', blockId || this.contextMenu.blockId);
            this.closeContextMenu();
        },

        deleteBlock(blockId) {
            this.$wire?.call('deleteBlock', blockId || this.contextMenu.blockId);
            this.closeContextMenu();
        },

        copyBlock(blockId) {
            this.$wire?.call('copyBlock', blockId || this.contextMenu.blockId);
            this.closeContextMenu();
        },

        pasteBlock(targetId, position = 'after') {
            this.$wire?.call('pasteBlock', targetId, position);
            this.closeContextMenu();
        },

        moveBlockUp(blockId) {
            this.$wire?.call('moveBlockUp', blockId || this.contextMenu.blockId);
            this.closeContextMenu();
        },

        moveBlockDown(blockId) {
            this.$wire?.call('moveBlockDown', blockId || this.contextMenu.blockId);
            this.closeContextMenu();
        },

        // Keyboard shortcuts
        handleKeyboard(e) {
            // Only handle if no input is focused
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
                return;
            }

            const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
            const cmdKey = isMac ? e.metaKey : e.ctrlKey;

            // Delete selected block
            if ((e.key === 'Delete' || e.key === 'Backspace') && this.selectedBlockId) {
                e.preventDefault();
                this.deleteBlock(this.selectedBlockId);
            }

            // Copy (Cmd/Ctrl + C)
            if (cmdKey && e.key === 'c' && this.selectedBlockId) {
                e.preventDefault();
                this.copyBlock(this.selectedBlockId);
            }

            // Paste (Cmd/Ctrl + V)
            if (cmdKey && e.key === 'v') {
                e.preventDefault();
                this.pasteBlock(this.selectedBlockId, 'after');
            }

            // Duplicate (Cmd/Ctrl + D)
            if (cmdKey && e.key === 'd' && this.selectedBlockId) {
                e.preventDefault();
                this.duplicateBlock(this.selectedBlockId);
            }

            // Undo (Cmd/Ctrl + Z)
            if (cmdKey && !e.shiftKey && e.key === 'z') {
                e.preventDefault();
                this.$wire?.call('undo');
            }

            // Redo (Cmd/Ctrl + Shift + Z)
            if (cmdKey && e.shiftKey && e.key === 'z') {
                e.preventDefault();
                this.$wire?.call('redo');
            }

            // Escape to deselect
            if (e.key === 'Escape') {
                this.deselectBlock();
                this.closeContextMenu();
            }
        },

        // Livewire event handlers
        onBlockAdded(data) {
            if (data.blockId) {
                this.selectBlock(data.blockId);
            }
        },

        onBlockUpdated(data) {
            // Re-render if needed
        },

        onBlockRemoved(data) {
            if (this.selectedBlockId === data.blockId) {
                this.deselectBlock();
            }
        },

        onLayoutLoaded(data) {
            this.blocks = data.blocks || [];
            this.deselectBlock();
        },
    }));

    /**
     * Block Panel Component
     */
    Alpine.data('visualEditorBlockPanel', () => ({
        search: '',
        expandedCategories: {},

        init() {
            // Expand all categories by default
            this.expandedCategories = {};
        },

        toggleCategory(categoryKey) {
            this.expandedCategories[categoryKey] = !this.expandedCategories[categoryKey];
        },

        isCategoryExpanded(categoryKey) {
            return this.expandedCategories[categoryKey] !== false;
        },

        filterBlocks(blocks) {
            if (!this.search) return blocks;
            const query = this.search.toLowerCase();
            return blocks.filter(block =>
                block.label.toLowerCase().includes(query) ||
                block.type.toLowerCase().includes(query)
            );
        },

        hasVisibleBlocks(blocks) {
            return this.filterBlocks(blocks).length > 0;
        },

        // Drag handlers for new blocks
        startDrag(e, blockType, blockLabel) {
            const canvas = Alpine.$data(document.querySelector('[x-data*="visualEditorCanvas"]'));
            if (canvas) {
                canvas.startDragNewBlock(e, blockType);
            }
        },
    }));

    /**
     * Layers Tree Component
     */
    Alpine.data('visualEditorLayersTree', () => ({
        expandedNodes: {},
        draggedNodeId: null,

        init() {
            // Expand root by default
        },

        toggleNode(nodeId) {
            this.expandedNodes[nodeId] = !this.expandedNodes[nodeId];
        },

        isNodeExpanded(nodeId) {
            return this.expandedNodes[nodeId] !== false;
        },

        selectNode(nodeId) {
            const canvas = Alpine.$data(document.querySelector('[x-data*="visualEditorCanvas"]'));
            if (canvas) {
                canvas.selectBlock(nodeId);
            }
        },

        isNodeSelected(nodeId) {
            const canvas = Alpine.$data(document.querySelector('[x-data*="visualEditorCanvas"]'));
            return canvas?.selectedBlockId === nodeId;
        },

        // Drag handlers for reordering
        startDrag(e, nodeId) {
            this.draggedNodeId = nodeId;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', JSON.stringify({
                type: 'move',
                blockId: nodeId,
            }));
        },

        handleDrop(e, targetId, position) {
            e.preventDefault();
            if (this.draggedNodeId && this.draggedNodeId !== targetId) {
                this.$wire?.call('moveBlock', this.draggedNodeId, targetId, position);
            }
            this.draggedNodeId = null;
        },

        endDrag() {
            this.draggedNodeId = null;
        },
    }));

    /**
     * Settings Panel Component
     */
    Alpine.data('visualEditorSettingsPanel', () => ({
        activeTab: 'content', // 'content', 'style', 'advanced'

        init() {
            // Default to content tab
        },

        setActiveTab(tab) {
            this.activeTab = tab;
        },

        isActiveTab(tab) {
            return this.activeTab === tab;
        },
    }));

    /**
     * AI Assistant Component
     */
    Alpine.data('visualEditorAI', () => ({
        prompt: '',
        isLoading: false,
        messages: [],

        init() {
            // Load previous messages if any
        },

        async submitPrompt() {
            if (!this.prompt.trim() || this.isLoading) return;

            const userMessage = this.prompt.trim();
            this.messages.push({ role: 'user', content: userMessage });
            this.prompt = '';
            this.isLoading = true;

            try {
                await this.$wire?.call('generateLayout', userMessage);
                // Response will be added via Livewire event
            } catch (error) {
                this.messages.push({
                    role: 'assistant',
                    content: 'Sorry, there was an error generating the layout. Please try again.',
                    error: true,
                });
            } finally {
                this.isLoading = false;
            }
        },

        addAssistantMessage(content) {
            this.messages.push({ role: 'assistant', content: content });
        },
    }));
});

/**
 * Utility: Sortable integration for drag and drop
 * Uses native HTML5 drag and drop API
 */
class VisualEditorSortable {
    constructor(container, options = {}) {
        this.container = container;
        this.options = {
            handle: options.handle || null,
            group: options.group || 'blocks',
            onSort: options.onSort || (() => {}),
            onAdd: options.onAdd || (() => {}),
        };

        this.init();
    }

    init() {
        this.container.addEventListener('dragstart', (e) => this.handleDragStart(e));
        this.container.addEventListener('dragover', (e) => this.handleDragOver(e));
        this.container.addEventListener('dragleave', (e) => this.handleDragLeave(e));
        this.container.addEventListener('drop', (e) => this.handleDrop(e));
        this.container.addEventListener('dragend', (e) => this.handleDragEnd(e));
    }

    handleDragStart(e) {
        const item = e.target.closest('[draggable="true"]');
        if (!item) return;

        item.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    }

    handleDragOver(e) {
        e.preventDefault();
        const dragging = this.container.querySelector('.dragging');
        const afterElement = this.getDragAfterElement(e.clientY);

        if (afterElement) {
            this.container.insertBefore(dragging, afterElement);
        } else {
            this.container.appendChild(dragging);
        }
    }

    handleDragLeave(e) {
        // Handle drag leave
    }

    handleDrop(e) {
        e.preventDefault();
        this.options.onSort(this.getOrder());
    }

    handleDragEnd(e) {
        const item = e.target.closest('[draggable="true"]');
        if (item) {
            item.classList.remove('dragging');
        }
    }

    getDragAfterElement(y) {
        const draggableElements = [...this.container.querySelectorAll('[draggable="true"]:not(.dragging)')];

        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    getOrder() {
        return [...this.container.querySelectorAll('[draggable="true"]')]
            .map(el => el.dataset.blockId);
    }
}

// Export for use in Livewire components
window.VisualEditorSortable = VisualEditorSortable;
