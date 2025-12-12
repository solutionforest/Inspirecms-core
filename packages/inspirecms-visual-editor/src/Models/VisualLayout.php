<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use SolutionForest\InspireCmsVisualEditor\Blocks\Registry\BlockRegistry;

class VisualLayout extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'layout_data' => 'array',
        'settings' => 'array',
    ];

    public function getTable(): string
    {
        return config('visual-editor.table_prefix', 'cms_') . 'visual_layouts';
    }

    /**
     * Get the parent layoutable model (Content, Template, etc.).
     */
    public function layoutable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the version history.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(VisualLayoutVersion::class, 'layout_id')->orderByDesc('created_at');
    }

    /**
     * Create a new version of this layout.
     */
    public function createVersion(?string $changeSummary = null): VisualLayoutVersion
    {
        $version = $this->versions()->create([
            'version' => $this->version ?? '1.0',
            'layout_data' => $this->layout_data,
            'settings' => $this->settings,
            'change_summary' => $changeSummary,
            'created_by' => auth()->id(),
        ]);

        // Increment version
        $this->increment('version');

        return $version;
    }

    /**
     * Restore from a specific version.
     */
    public function restoreVersion(VisualLayoutVersion $version): self
    {
        $this->update([
            'layout_data' => $version->layout_data,
            'settings' => $version->settings,
        ]);

        return $this;
    }

    /**
     * Get the root block of the layout.
     */
    public function getRootBlock(): ?array
    {
        return $this->layout_data['root'] ?? null;
    }

    /**
     * Set the root block of the layout.
     */
    public function setRootBlock(array $block): self
    {
        $layoutData = $this->layout_data ?? [];
        $layoutData['root'] = $block;
        $this->layout_data = $layoutData;

        return $this;
    }

    /**
     * Find a block by ID within the layout.
     */
    public function findBlock(string $blockId): ?array
    {
        return $this->findBlockRecursive($this->getRootBlock(), $blockId);
    }

    /**
     * Recursively find a block by ID.
     */
    protected function findBlockRecursive(?array $block, string $blockId): ?array
    {
        if (! $block) {
            return null;
        }

        if (($block['id'] ?? null) === $blockId) {
            return $block;
        }

        foreach ($block['children'] ?? [] as $child) {
            $found = $this->findBlockRecursive($child, $blockId);
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    /**
     * Update a block within the layout.
     */
    public function updateBlock(string $blockId, array $updates): bool
    {
        $layoutData = $this->layout_data ?? [];

        if (! isset($layoutData['root'])) {
            return false;
        }

        $layoutData['root'] = $this->updateBlockRecursive($layoutData['root'], $blockId, $updates);
        $this->layout_data = $layoutData;

        return true;
    }

    /**
     * Recursively update a block.
     */
    protected function updateBlockRecursive(array $block, string $blockId, array $updates): array
    {
        if (($block['id'] ?? null) === $blockId) {
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

    /**
     * Remove a block from the layout.
     */
    public function removeBlock(string $blockId): bool
    {
        $layoutData = $this->layout_data ?? [];

        if (! isset($layoutData['root'])) {
            return false;
        }

        // Can't remove root
        if (($layoutData['root']['id'] ?? null) === $blockId) {
            return false;
        }

        $layoutData['root'] = $this->removeBlockRecursive($layoutData['root'], $blockId);
        $this->layout_data = $layoutData;

        return true;
    }

    /**
     * Recursively remove a block.
     */
    protected function removeBlockRecursive(array $block, string $blockId): array
    {
        if (isset($block['children'])) {
            $block['children'] = array_values(array_filter(
                $block['children'],
                fn ($child) => ($child['id'] ?? null) !== $blockId
            ));

            $block['children'] = array_map(
                fn ($child) => $this->removeBlockRecursive($child, $blockId),
                $block['children']
            );
        }

        return $block;
    }

    /**
     * Add a block as a child of another block.
     */
    public function addBlock(string $parentId, array $block, ?int $position = null): bool
    {
        $layoutData = $this->layout_data ?? [];

        if (! isset($layoutData['root'])) {
            return false;
        }

        $layoutData['root'] = $this->addBlockRecursive($layoutData['root'], $parentId, $block, $position);
        $this->layout_data = $layoutData;

        return true;
    }

    /**
     * Recursively add a block to a parent.
     */
    protected function addBlockRecursive(array $parent, string $parentId, array $block, ?int $position): array
    {
        if (($parent['id'] ?? null) === $parentId) {
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
                fn ($child) => $this->addBlockRecursive($child, $parentId, $block, $position),
                $parent['children']
            );
        }

        return $parent;
    }

    /**
     * Move a block to a new parent/position.
     */
    public function moveBlock(string $blockId, string $newParentId, ?int $position = null): bool
    {
        $block = $this->findBlock($blockId);

        if (! $block) {
            return false;
        }

        // Remove from current position
        $this->removeBlock($blockId);

        // Add to new position
        return $this->addBlock($newParentId, $block, $position);
    }

    /**
     * Duplicate a block.
     */
    public function duplicateBlock(string $blockId): ?array
    {
        $block = $this->findBlock($blockId);

        if (! $block) {
            return null;
        }

        // Generate new IDs for the block and all children
        $newBlock = $this->regenerateBlockIds($block);

        // Find parent and position
        $parentInfo = $this->findParentInfo($blockId);

        if ($parentInfo) {
            $position = $parentInfo['position'] + 1;
            $this->addBlock($parentInfo['parentId'], $newBlock, $position);
        }

        return $newBlock;
    }

    /**
     * Regenerate IDs for a block and all its children.
     */
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

    /**
     * Find parent info for a block.
     */
    protected function findParentInfo(string $blockId, ?array $parent = null, int $position = 0): ?array
    {
        $parent = $parent ?? $this->getRootBlock();

        if (! $parent) {
            return null;
        }

        foreach ($parent['children'] ?? [] as $index => $child) {
            if (($child['id'] ?? null) === $blockId) {
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

    /**
     * Validate the layout structure.
     */
    public function validate(): array
    {
        return BlockRegistry::validateLayout($this->layout_data ?? []);
    }

    /**
     * Render the layout for preview.
     */
    public function render(): string
    {
        $root = $this->getRootBlock();

        if (! $root) {
            return '';
        }

        return $this->renderBlock($root);
    }

    /**
     * Render a single block and its children.
     */
    protected function renderBlock(array $block): string
    {
        $blockType = BlockRegistry::get($block['type'] ?? '');

        if (! $blockType) {
            return '';
        }

        $props = $blockType->transformPropsFromStorage($block['props'] ?? []);
        $styles = $block['styles'] ?? [];

        // Render children
        $childrenHtml = [];
        foreach ($block['children'] ?? [] as $child) {
            $childrenHtml[] = $this->renderBlock($child);
        }

        return $blockType->render($props, $childrenHtml, $styles);
    }

    /**
     * Render the layout for frontend.
     */
    public function renderFrontend(): string
    {
        $root = $this->getRootBlock();

        if (! $root) {
            return '';
        }

        return $this->renderBlockFrontend($root);
    }

    /**
     * Render a single block for frontend.
     */
    protected function renderBlockFrontend(array $block): string
    {
        $blockType = BlockRegistry::get($block['type'] ?? '');

        if (! $blockType) {
            return '';
        }

        $props = $blockType->transformPropsFromStorage($block['props'] ?? []);
        $styles = $block['styles'] ?? [];

        // Render children
        $childrenHtml = [];
        foreach ($block['children'] ?? [] as $child) {
            $childrenHtml[] = $this->renderBlockFrontend($child);
        }

        return $blockType->renderFrontend($props, $childrenHtml, $styles);
    }

    /**
     * Create a default empty layout.
     */
    public static function createEmpty(?string $name = null): self
    {
        return new static([
            'name' => $name ?? 'New Layout',
            'type' => 'page',
            'status' => 'draft',
            'version' => '1.0',
            'layout_data' => [
                'version' => '1.0',
                'root' => BlockRegistry::createBlockData('container'),
            ],
            'settings' => [
                'responsive' => true,
                'maxWidth' => '1200px',
            ],
        ]);
    }
}
