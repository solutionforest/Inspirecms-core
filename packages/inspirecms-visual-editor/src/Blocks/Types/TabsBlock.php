<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Blocks\Types;

use SolutionForest\InspireCmsVisualEditor\Enums\BlockCategory;

class TabsBlock extends AbstractContainerBlock
{
    public function getType(): string
    {
        return 'tabs';
    }

    public function getLabel(): string
    {
        return 'Tabs';
    }

    public function getCategory(): string
    {
        return BlockCategory::INTERACTIVE->value;
    }

    public function getIcon(): string
    {
        return 'heroicon-o-rectangle-stack';
    }

    public function getDescription(): string
    {
        return 'Tabbed content panels for organized information';
    }

    public function getAllowedChildren(): array
    {
        return ['tab-panel'];
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                'name' => 'tabs',
                'type' => 'repeater',
                'label' => 'Tabs',
                'schema' => [
                    ['name' => 'id', 'type' => 'hidden'],
                    ['name' => 'label', 'type' => 'text', 'label' => 'Tab Label'],
                    ['name' => 'icon', 'type' => 'icon', 'label' => 'Icon'],
                ],
            ],
            [
                'name' => 'defaultTab',
                'type' => 'number',
                'label' => 'Default Active Tab',
                'description' => 'Index of the default active tab (starting from 0)',
                'default' => 0,
            ],
            [
                'name' => 'orientation',
                'type' => 'select',
                'label' => 'Tab Orientation',
                'options' => [
                    'horizontal' => 'Horizontal',
                    'vertical' => 'Vertical',
                ],
                'default' => 'horizontal',
            ],
            [
                'name' => 'alignment',
                'type' => 'select',
                'label' => 'Tab Alignment',
                'options' => [
                    'start' => 'Start',
                    'center' => 'Center',
                    'end' => 'End',
                    'stretch' => 'Stretch (Full Width)',
                ],
                'default' => 'start',
            ],
            [
                'name' => 'variant',
                'type' => 'select',
                'label' => 'Style Variant',
                'options' => [
                    'line' => 'Underline',
                    'enclosed' => 'Enclosed',
                    'pills' => 'Pills',
                    'unstyled' => 'Unstyled',
                ],
                'default' => 'line',
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
            'tabs' => [
                ['id' => 'tab_1', 'label' => 'Tab 1', 'icon' => ''],
                ['id' => 'tab_2', 'label' => 'Tab 2', 'icon' => ''],
            ],
            'defaultTab' => 0,
            'orientation' => 'horizontal',
            'alignment' => 'start',
            'variant' => 'line',
            'animated' => true,
        ];
    }
}
