<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use SolutionForest\InspireCmsVisualEditor\Blocks\Registry\BlockRegistry;
use SolutionForest\InspireCmsVisualEditor\Models\VisualLayout;

class VisualEditor extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?string $layoutId = null;

    public array $layoutData = [];

    public array $settings = [];

    public ?string $selectedBlockId = null;

    public ?string $hoveredBlockId = null;

    public string $previewMode = 'desktop'; // desktop, tablet, mobile

    public bool $showLayers = true;

    public bool $showBlocks = false;

    public bool $showSettings = true;

    public bool $showAI = false;

    public array $clipboard = [];

    public array $history = [];

    public int $historyIndex = -1;

    public bool $isDirty = false;

    public function mount(?string $layoutId = null, ?array $initialData = null): void
    {
        if ($layoutId) {
            $this->layoutId = $layoutId;
            $layout = VisualLayout::find($layoutId);

            if ($layout) {
                $this->layoutData = $layout->layout_data ?? [];
                $this->settings = $layout->settings ?? [];
            }
        } elseif ($initialData) {
            $this->layoutData = $initialData;
        } else {
            // Create default empty layout
            $this->layoutData = [
                'version' => '1.0',
                'root' => BlockRegistry::createBlockData('container'),
            ];
        }

        $this->pushToHistory();
    }

    #[Computed]
    public function blocks(): array
    {
        return BlockRegistry::getBlocksForPanel();
    }

    #[Computed]
    public function selectedBlock(): ?array
    {
        if (! $this->selectedBlockId) {
            return null;
        }

        return $this->findBlock($this->selectedBlockId);
    }

    #[Computed]
    public function blockTree(): array
    {
        return $this->buildBlockTree($this->layoutData['root'] ?? []);
    }

    protected function buildBlockTree(array $block, int $depth = 0): array
    {
        $blockType = BlockRegistry::get($block['type'] ?? '');

        $node = [
            'id' => $block['id'] ?? '',
            'type' => $block['type'] ?? 'unknown',
            'label' => $blockType?->getLabel() ?? ucfirst($block['type'] ?? 'Block'),
            'icon' => $blockType?->getIcon() ?? 'heroicon-o-cube',
            'isContainer' => $blockType?->isContainer() ?? false,
            'depth' => $depth,
            'props' => $block['props'] ?? [],
            'children' => [],
        ];

        foreach ($block['children'] ?? [] as $child) {
            $node['children'][] = $this->buildBlockTree($child, $depth + 1);
        }

        return $node;
    }

    public function selectBlock(?string $blockId): void
    {
        $this->selectedBlockId = $blockId;

        if ($blockId) {
            $this->showSettings = true;
        }
    }

    public function hoverBlock(?string $blockId): void
    {
        $this->hoveredBlockId = $blockId;
    }

    public function setPreviewMode(string $mode): void
    {
        $this->previewMode = $mode;
    }

    public function togglePanel(string $panel): void
    {
        match ($panel) {
            'layers' => $this->showLayers = ! $this->showLayers,
            'blocks' => $this->showBlocks = ! $this->showBlocks,
            'settings' => $this->showSettings = ! $this->showSettings,
            'ai' => $this->showAI = ! $this->showAI,
            default => null,
        };
    }

    #[On('add-block')]
    public function addBlock(string $type, ?string $parentId = null, ?int $position = null): void
    {
        $parentId = $parentId ?? $this->layoutData['root']['id'] ?? null;

        if (! $parentId) {
            return;
        }

        $newBlock = BlockRegistry::createBlockData($type);

        if (! $newBlock) {
            Notification::make()
                ->title('Unknown block type')
                ->danger()
                ->send();

            return;
        }

        $this->layoutData['root'] = $this->addBlockToParent(
            $this->layoutData['root'],
            $parentId,
            $newBlock,
            $position
        );

        $this->selectBlock($newBlock['id']);
        $this->pushToHistory();
        $this->isDirty = true;

        $this->dispatch('block-added', blockId: $newBlock['id']);
    }

    protected function addBlockToParent(array $parent, string $parentId, array $block, ?int $position): array
    {
        if ($parent['id'] === $parentId) {
            $parent['children'] = $parent['children'] ?? [];

            if ($position === null) {
                $parent['children'][] = $block;
            } else {
                array_splice($parent['children'], $position, 0, [$block]);
            }

            return $parent;
        }

        if (isset($parent['children'])) {
            $parent['children'] = array_map(
                fn ($child) => $this->addBlockToParent($child, $parentId, $block, $position),
                $parent['children']
            );
        }

        return $parent;
    }

    #[On('update-block')]
    public function updateBlock(string $blockId, array $updates): void
    {
        $this->layoutData['root'] = $this->updateBlockRecursive(
            $this->layoutData['root'],
            $blockId,
            $updates
        );

        $this->pushToHistory();
        $this->isDirty = true;

        $this->dispatch('block-updated', blockId: $blockId);
    }

    protected function updateBlockRecursive(array $block, string $blockId, array $updates): array
    {
        if ($block['id'] === $blockId) {
            // Merge props
            if (isset($updates['props'])) {
                $block['props'] = array_merge($block['props'] ?? [], $updates['props']);
                unset($updates['props']);
            }

            // Merge styles
            if (isset($updates['styles'])) {
                $block['styles'] = array_merge($block['styles'] ?? [], $updates['styles']);
                unset($updates['styles']);
            }

            return array_merge($block, $updates);
        }

        if (isset($block['children'])) {
            $block['children'] = array_map(
                fn ($child) => $this->updateBlockRecursive($child, $blockId, $updates),
                $block['children']
            );
        }

        return $block;
    }

    #[On('delete-block')]
    public function deleteBlock(string $blockId): void
    {
        // Can't delete root
        if ($this->layoutData['root']['id'] === $blockId) {
            Notification::make()
                ->title('Cannot delete root container')
                ->warning()
                ->send();

            return;
        }

        $this->layoutData['root'] = $this->removeBlockRecursive(
            $this->layoutData['root'],
            $blockId
        );

        if ($this->selectedBlockId === $blockId) {
            $this->selectedBlockId = null;
        }

        $this->pushToHistory();
        $this->isDirty = true;

        $this->dispatch('block-deleted', blockId: $blockId);
    }

    protected function removeBlockRecursive(array $block, string $blockId): array
    {
        if (isset($block['children'])) {
            $block['children'] = array_values(array_filter(
                $block['children'],
                fn ($child) => $child['id'] !== $blockId
            ));

            $block['children'] = array_map(
                fn ($child) => $this->removeBlockRecursive($child, $blockId),
                $block['children']
            );
        }

        return $block;
    }

    #[On('duplicate-block')]
    public function duplicateBlock(string $blockId): void
    {
        $block = $this->findBlock($blockId);

        if (! $block) {
            return;
        }

        $parentInfo = $this->findParentInfo($blockId);

        if (! $parentInfo) {
            return;
        }

        $newBlock = $this->regenerateBlockIds($block);
        $position = $parentInfo['position'] + 1;

        $this->layoutData['root'] = $this->addBlockToParent(
            $this->layoutData['root'],
            $parentInfo['parentId'],
            $newBlock,
            $position
        );

        $this->selectBlock($newBlock['id']);
        $this->pushToHistory();
        $this->isDirty = true;

        $this->dispatch('block-duplicated', blockId: $newBlock['id']);
    }

    protected function regenerateBlockIds(array $block): array
    {
        $block['id'] = BlockRegistry::generateBlockId();

        if (isset($block['children'])) {
            $block['children'] = array_map(
                fn ($child) => $this->regenerateBlockIds($child),
                $block['children']
            );
        }

        return $block;
    }

    #[On('move-block')]
    public function moveBlock(string $blockId, string $newParentId, int $position): void
    {
        $block = $this->findBlock($blockId);

        if (! $block) {
            return;
        }

        // Remove from current position
        $this->layoutData['root'] = $this->removeBlockRecursive($this->layoutData['root'], $blockId);

        // Add to new position
        $this->layoutData['root'] = $this->addBlockToParent(
            $this->layoutData['root'],
            $newParentId,
            $block,
            $position
        );

        $this->pushToHistory();
        $this->isDirty = true;

        $this->dispatch('block-moved', blockId: $blockId);
    }

    public function copyBlock(?string $blockId = null): void
    {
        $blockId = $blockId ?? $this->selectedBlockId;

        if (! $blockId) {
            return;
        }

        $block = $this->findBlock($blockId);

        if ($block) {
            $this->clipboard = $block;

            Notification::make()
                ->title('Block copied')
                ->success()
                ->send();
        }
    }

    public function pasteBlock(?string $parentId = null): void
    {
        if (empty($this->clipboard)) {
            Notification::make()
                ->title('Nothing to paste')
                ->warning()
                ->send();

            return;
        }

        $parentId = $parentId ?? $this->selectedBlockId ?? $this->layoutData['root']['id'];
        $newBlock = $this->regenerateBlockIds($this->clipboard);

        $this->layoutData['root'] = $this->addBlockToParent(
            $this->layoutData['root'],
            $parentId,
            $newBlock,
            null
        );

        $this->selectBlock($newBlock['id']);
        $this->pushToHistory();
        $this->isDirty = true;

        Notification::make()
            ->title('Block pasted')
            ->success()
            ->send();
    }

    protected function findBlock(string $blockId, ?array $block = null): ?array
    {
        $block = $block ?? $this->layoutData['root'] ?? null;

        if (! $block) {
            return null;
        }

        if ($block['id'] === $blockId) {
            return $block;
        }

        foreach ($block['children'] ?? [] as $child) {
            $found = $this->findBlock($blockId, $child);
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    protected function findParentInfo(string $blockId, ?array $parent = null, int $position = 0): ?array
    {
        $parent = $parent ?? $this->layoutData['root'] ?? null;

        if (! $parent) {
            return null;
        }

        foreach ($parent['children'] ?? [] as $index => $child) {
            if ($child['id'] === $blockId) {
                return [
                    'parentId' => $parent['id'],
                    'position' => $index,
                ];
            }

            $found = $this->findParentInfo($blockId, $child, $index);
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    protected function pushToHistory(): void
    {
        // Remove any history after current index
        $this->history = array_slice($this->history, 0, $this->historyIndex + 1);

        // Add current state
        $this->history[] = json_encode($this->layoutData);
        $this->historyIndex = count($this->history) - 1;

        // Limit history size
        if (count($this->history) > 50) {
            array_shift($this->history);
            $this->historyIndex--;
        }
    }

    public function undo(): void
    {
        if ($this->historyIndex > 0) {
            $this->historyIndex--;
            $this->layoutData = json_decode($this->history[$this->historyIndex], true);
            $this->isDirty = true;

            $this->dispatch('layout-changed');
        }
    }

    public function redo(): void
    {
        if ($this->historyIndex < count($this->history) - 1) {
            $this->historyIndex++;
            $this->layoutData = json_decode($this->history[$this->historyIndex], true);
            $this->isDirty = true;

            $this->dispatch('layout-changed');
        }
    }

    public function canUndo(): bool
    {
        return $this->historyIndex > 0;
    }

    public function canRedo(): bool
    {
        return $this->historyIndex < count($this->history) - 1;
    }

    public function save(): void
    {
        if ($this->layoutId) {
            $layout = VisualLayout::find($this->layoutId);

            if ($layout) {
                $layout->createVersion('Auto-save before update');

                $layout->update([
                    'layout_data' => $this->layoutData,
                    'settings' => $this->settings,
                    'updated_by' => auth()->id(),
                ]);
            }
        }

        $this->isDirty = false;

        Notification::make()
            ->title('Layout saved')
            ->success()
            ->send();

        $this->dispatch('layout-saved', layoutData: $this->layoutData);
    }

    public function getRenderedPreview(): string
    {
        $layout = new VisualLayout([
            'layout_data' => $this->layoutData,
        ]);

        return $layout->render();
    }

    public function saveAction(): Action
    {
        return Action::make('save')
            ->label('Save')
            ->icon('heroicon-o-cloud-arrow-up')
            ->action(fn () => $this->save())
            ->keyBindings(['mod+s']);
    }

    public function render(): View
    {
        return view('visual-editor::livewire.visual-editor');
    }
}
