<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use SolutionForest\InspireCmsVisualEditor\Blocks\Registry\BlockRegistry;

class VisualEditorField extends Field
{
    protected string $view = 'visual-editor::filament.visual-editor-field';

    protected ?string $layoutId = null;

    protected array $initialData = [];

    protected int $minHeight = 600;

    protected bool $fullscreen = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (VisualEditorField $component, $state) {
            if (is_array($state)) {
                $component->initialData($state);
            } elseif (is_string($state)) {
                $decoded = json_decode($state, true);
                if ($decoded) {
                    $component->initialData($decoded);
                }
            }
        });

        $this->dehydrateStateUsing(function ($state) {
            if (is_array($state)) {
                return $state;
            }

            return json_decode($state, true) ?? [];
        });
    }

    public function layoutId(?string $layoutId): static
    {
        $this->layoutId = $layoutId;

        return $this;
    }

    public function getLayoutId(): ?string
    {
        return $this->layoutId;
    }

    public function initialData(array $data): static
    {
        $this->initialData = $data;

        return $this;
    }

    public function getInitialData(): array
    {
        if (! empty($this->initialData)) {
            return $this->initialData;
        }

        // Return default empty layout
        return [
            'version' => '1.0',
            'root' => BlockRegistry::createBlockData('container'),
        ];
    }

    public function minHeight(int $height): static
    {
        $this->minHeight = $height;

        return $this;
    }

    public function getMinHeight(): int
    {
        return $this->minHeight;
    }

    public function fullscreen(bool $fullscreen = true): static
    {
        $this->fullscreen = $fullscreen;

        return $this;
    }

    public function isFullscreen(): bool
    {
        return $this->fullscreen;
    }

    public function getBlocks(): array
    {
        return BlockRegistry::getBlocksForPanel();
    }
}
