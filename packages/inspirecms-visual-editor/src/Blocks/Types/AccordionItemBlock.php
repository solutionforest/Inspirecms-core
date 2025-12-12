<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Blocks\Types;

use SolutionForest\InspireCmsVisualEditor\Enums\BlockCategory;

class AccordionItemBlock extends AbstractContainerBlock
{
    public function getType(): string
    {
        return 'accordion-item';
    }

    public function getLabel(): string
    {
        return 'Accordion Item';
    }

    public function getCategory(): string
    {
        return BlockCategory::INTERACTIVE->value;
    }

    public function getIcon(): string
    {
        return 'heroicon-o-chevron-down';
    }

    public function getDescription(): string
    {
        return 'A single collapsible item in an accordion';
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                'name' => 'title',
                'type' => 'text',
                'label' => 'Title',
                'required' => true,
            ],
            [
                'name' => 'subtitle',
                'type' => 'text',
                'label' => 'Subtitle',
            ],
            [
                'name' => 'icon',
                'type' => 'icon',
                'label' => 'Icon',
            ],
            [
                'name' => 'disabled',
                'type' => 'toggle',
                'label' => 'Disabled',
                'default' => false,
            ],
        ];
    }

    public function getDefaultProps(): array
    {
        return [
            'title' => 'Accordion Item',
            'subtitle' => '',
            'icon' => '',
            'disabled' => false,
        ];
    }
}
