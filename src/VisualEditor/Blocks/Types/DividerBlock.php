<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\Blocks\Types;

use SolutionForest\InspireCms\VisualEditor\Enums\BlockCategory;

class DividerBlock extends AbstractBlock
{
    protected string $type = 'divider';

    protected BlockCategory $category = BlockCategory::LAYOUT;

    protected string $label = 'Divider';

    protected string $icon = 'heroicon-o-minus';

    protected string $description = 'A horizontal line to separate content';

    public function getDefaultProps(): array
    {
        return [
            'style' => 'solid',
            'width' => '100%',
            'thickness' => '1px',
            'color' => '#e5e7eb',
            'marginY' => '24px',
        ];
    }

    public function getSettingsSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Divider Settings')
                ->schema([
                    \Filament\Forms\Components\Select::make('props.style')
                        ->label('Style')
                        ->options([
                            'solid' => 'Solid',
                            'dashed' => 'Dashed',
                            'dotted' => 'Dotted',
                            'double' => 'Double',
                        ])
                        ->default('solid'),
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('props.width')
                                ->label('Width')
                                ->placeholder('100%'),
                            \Filament\Forms\Components\TextInput::make('props.thickness')
                                ->label('Thickness')
                                ->placeholder('1px'),
                        ]),
                    \Filament\Forms\Components\ColorPicker::make('props.color')
                        ->label('Color'),
                    \Filament\Forms\Components\TextInput::make('props.marginY')
                        ->label('Vertical Margin')
                        ->placeholder('24px'),
                ]),
        ];
    }

    public function getStyleSchema(): array
    {
        return [];
    }

    protected function renderDefault(array $props, array $children, array $styles, bool $isEditor): string
    {
        $style = $props['style'] ?? 'solid';
        $width = $props['width'] ?? '100%';
        $thickness = $props['thickness'] ?? '1px';
        $color = $props['color'] ?? '#e5e7eb';
        $marginY = $props['marginY'] ?? '24px';

        $editorClass = $isEditor ? 've-divider' : '';

        $cssStyle = sprintf(
            'border: none; border-top: %s %s %s; width: %s; margin: %s auto;',
            $thickness,
            $style,
            $color,
            $width,
            $marginY
        );

        return sprintf(
            '<hr class="%s" data-block-type="%s" style="%s" />',
            e($editorClass),
            e($this->type),
            e($cssStyle)
        );
    }
}
