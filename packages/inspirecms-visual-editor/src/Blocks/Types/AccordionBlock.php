<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Blocks\Types;

use SolutionForest\InspireCmsVisualEditor\Enums\BlockCategory;

class AccordionBlock extends AbstractContainerBlock
{
    public function getType(): string
    {
        return 'accordion';
    }

    public function getLabel(): string
    {
        return 'Accordion';
    }

    public function getCategory(): string
    {
        return BlockCategory::INTERACTIVE->value;
    }

    public function getIcon(): string
    {
        return 'heroicon-o-bars-3-bottom-left';
    }

    public function getDescription(): string
    {
        return 'Collapsible content sections';
    }

    public function getAllowedChildren(): array
    {
        return ['accordion-item'];
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                'name' => 'allowMultiple',
                'type' => 'toggle',
                'label' => 'Allow Multiple Open',
                'description' => 'Allow multiple items to be open at once',
                'default' => false,
            ],
            [
                'name' => 'defaultOpen',
                'type' => 'text',
                'label' => 'Default Open Items',
                'description' => 'Comma-separated list of item indices to open by default (e.g., "0,1")',
                'default' => '0',
            ],
            [
                'name' => 'variant',
                'type' => 'select',
                'label' => 'Style Variant',
                'options' => [
                    'default' => 'Default',
                    'bordered' => 'Bordered',
                    'separated' => 'Separated',
                    'flush' => 'Flush (No borders)',
                ],
                'default' => 'default',
            ],
            [
                'name' => 'iconPosition',
                'type' => 'select',
                'label' => 'Icon Position',
                'options' => [
                    'left' => 'Left',
                    'right' => 'Right',
                ],
                'default' => 'right',
            ],
            [
                'name' => 'animated',
                'type' => 'toggle',
                'label' => 'Animate Transitions',
                'default' => true,
            ],
        ];
    }

    public function getDefaultProps(): array
    {
        return [
            'allowMultiple' => false,
            'defaultOpen' => '0',
            'variant' => 'default',
            'iconPosition' => 'right',
            'animated' => true,
        ];
    }
}
