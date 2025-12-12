<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class LayersPanel extends Component
{
    public array $blockTree = [];

    public ?string $selectedBlockId = null;

    public ?string $hoveredBlockId = null;

    public array $expandedNodes = [];

    public function mount(array $blockTree = []): void
    {
        $this->blockTree = $blockTree;

        // Expand root by default
        if (isset($blockTree['id'])) {
            $this->expandedNodes[$blockTree['id']] = true;
        }
    }

    #[On('update-tree')]
    public function updateTree(array $blockTree): void
    {
        $this->blockTree = $blockTree;
    }

    #[On('update-selection')]
    public function updateSelection(?string $blockId): void
    {
        $this->selectedBlockId = $blockId;

        // Auto-expand parents when selecting
        if ($blockId) {
            $this->expandParents($blockId);
        }
    }

    protected function expandParents(string $blockId): void
    {
        $path = $this->findBlockPath($this->blockTree, $blockId);

        foreach ($path as $id) {
            $this->expandedNodes[$id] = true;
        }
    }

    protected function findBlockPath(array $block, string $targetId, array $path = []): array
    {
        if ($block['id'] === $targetId) {
            return $path;
        }

        foreach ($block['children'] ?? [] as $child) {
            $result = $this->findBlockPath($child, $targetId, [...$path, $block['id']]);
            if (! empty($result)) {
                return $result;
            }
        }

        return [];
    }

    public function toggleExpand(string $blockId): void
    {
        if (isset($this->expandedNodes[$blockId])) {
            unset($this->expandedNodes[$blockId]);
        } else {
            $this->expandedNodes[$blockId] = true;
        }
    }

    public function selectBlock(string $blockId): void
    {
        $this->selectedBlockId = $blockId;
        $this->dispatch('select-block', blockId: $blockId)->to('visual-editor');
    }

    public function hoverBlock(?string $blockId): void
    {
        $this->hoveredBlockId = $blockId;
        $this->dispatch('hover-block', blockId: $blockId)->to('visual-editor');
    }

    public function deleteBlock(string $blockId): void
    {
        $this->dispatch('delete-block', blockId: $blockId)->to('visual-editor');
    }

    public function duplicateBlock(string $blockId): void
    {
        $this->dispatch('duplicate-block', blockId: $blockId)->to('visual-editor');
    }

    public function moveBlock(string $blockId, string $newParentId, int $position): void
    {
        $this->dispatch('move-block', blockId: $blockId, newParentId: $newParentId, position: $position)->to('visual-editor');
    }

    public function render(): View
    {
        return view('visual-editor::livewire.layers-panel');
    }
}
