<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Blocks\Types;

use SolutionForest\InspireCmsVisualEditor\Enums\BlockCategory;

class TabPanelBlock extends AbstractContainerBlock
{
    public function getType(): string
    {
        return 'tab-panel';
    }

    public function getLabel(): string
    {
        return 'Tab Panel';
    }

    public function getCategory(): string
    {
        return BlockCategory::INTERACTIVE->value;
    }

    public function getIcon(): string
    {
        return 'heroicon-o-square-3-stack-3d';
    }

    public function getDescription(): string
    {
        return 'Content panel for a tab';
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                'name' => 'tabId',
                'type' => 'text',
                'label' => 'Tab ID',
                'description' => 'Links this panel to a tab in the parent Tabs block',
            ],
        ];
    }

    public function getDefaultProps(): array
    {
        return [
            'tabId' => '',
        ];
    }
}
