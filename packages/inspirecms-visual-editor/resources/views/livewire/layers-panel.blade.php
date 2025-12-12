<div class="ve-layers-panel h-full flex flex-col">
    {{-- Header --}}
    <div class="p-3 border-b border-gray-200 dark:border-gray-700">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Page Structure</div>
    </div>

    {{-- Tree --}}
    <div class="flex-1 overflow-auto p-2">
        @if(!empty($blockTree))
            <div x-data="layersTree()" class="ve-layers-tree">
                @include('visual-editor::components.layer-node', ['node' => $blockTree, 'depth' => 0])
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <x-heroicon-o-document class="w-12 h-12 text-gray-300 mb-3" />
                <p class="text-sm text-gray-500">Empty page</p>
                <p class="text-xs text-gray-400 mt-1">Add blocks to build your layout</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('layersTree', () => ({
        expandedNodes: @json($expandedNodes),
        selectedBlockId: @entangle('selectedBlockId'),
        hoveredBlockId: @entangle('hoveredBlockId'),

        toggleExpand(nodeId) {
            if (this.expandedNodes[nodeId]) {
                delete this.expandedNodes[nodeId];
            } else {
                this.expandedNodes[nodeId] = true;
            }
            $wire.toggleExpand(nodeId);
        },

        isExpanded(nodeId) {
            return this.expandedNodes[nodeId] === true;
        },

        selectNode(nodeId) {
            this.selectedBlockId = nodeId;
            $wire.selectBlock(nodeId);
        },

        hoverNode(nodeId) {
            this.hoveredBlockId = nodeId;
            $wire.hoverBlock(nodeId);
        },

        leaveNode() {
            this.hoveredBlockId = null;
            $wire.hoverBlock(null);
        }
    }));
});
</script>
@endpush
