<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\Blocks\Contracts;

use Illuminate\Contracts\View\View;

interface BlockInterface
{
    /**
     * Get the unique type identifier for this block.
     */
    public function getType(): string;

    /**
     * Get the category this block belongs to.
     */
    public function getCategory(): string;

    /**
     * Get the display name for the block.
     */
    public function getLabel(): string;

    /**
     * Get the icon for the block (Heroicon name or SVG).
     */
    public function getIcon(): string;

    /**
     * Get the description for the block.
     */
    public function getDescription(): string;

    /**
     * Get the default properties for a new instance of this block.
     */
    public function getDefaultProps(): array;

    /**
     * Get the Filament form schema for the block's settings panel.
     */
    public function getSettingsSchema(): array;

    /**
     * Get the Filament form schema for the block's style/design panel.
     */
    public function getStyleSchema(): array;

    /**
     * Whether this block can contain child blocks.
     */
    public function isContainer(): bool;

    /**
     * Get allowed child block types (empty array means all types allowed).
     */
    public function getAllowedChildren(): array;

    /**
     * Get the maximum number of children (null for unlimited).
     */
    public function getMaxChildren(): ?int;

    /**
     * Get the preview/thumbnail image for the block library.
     */
    public function getPreviewImage(): ?string;

    /**
     * Render the block for the canvas/preview.
     */
    public function render(array $props, array $children = [], array $styles = []): View|string;

    /**
     * Render the block for the frontend.
     */
    public function renderFrontend(array $props, array $children = [], array $styles = []): View|string;

    /**
     * Validate the block's properties.
     */
    public function validateProps(array $props): array;

    /**
     * Transform props before saving.
     */
    public function transformPropsForStorage(array $props): array;

    /**
     * Transform props after loading from storage.
     */
    public function transformPropsFromStorage(array $props): array;
}
