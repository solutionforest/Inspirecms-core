<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\Blocks\Types;

use SolutionForest\InspireCms\VisualEditor\Enums\BlockCategory;

class ColumnBlock extends AbstractContainerBlock
{
    protected string $type = 'column';

    protected BlockCategory $category = BlockCategory::LAYOUT;

    protected string $label = 'Column';

    protected string $icon = 'heroicon-o-view-columns';

    protected string $description = 'A column for use within grids or flex containers';

    public function getDefaultProps(): array
    {
        return [
            'display' => 'flex',
            'flexDirection' => 'column',
            'justifyContent' => 'flex-start',
            'alignItems' => 'stretch',
            'gap' => '16px',
            'span' => 1,
            'spanMobile' => null,
            'spanTablet' => null,
        ];
    }

    public function getSettingsSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Column Settings')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('props.span')
                        ->label('Column Span')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->maxValue(12)
                        ->helperText('How many grid columns to span'),
                    \Filament\Forms\Components\TextInput::make('props.rowSpan')
                        ->label('Row Span')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->helperText('How many grid rows to span'),
                ]),
            \Filament\Forms\Components\Section::make('Responsive Spans')
                ->collapsed()
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('props.spanMobile')
                                ->label('Mobile Span')
                                ->numeric()
                                ->placeholder('full'),
                            \Filament\Forms\Components\TextInput::make('props.spanTablet')
                                ->label('Tablet Span')
                                ->numeric()
                                ->placeholder('inherit'),
                        ]),
                ]),
            \Filament\Forms\Components\Section::make('Content Alignment')
                ->collapsed()
                ->schema([
                    \Filament\Forms\Components\Select::make('props.justifyContent')
                        ->label('Vertical Alignment')
                        ->options([
                            'flex-start' => 'Top',
                            'center' => 'Center',
                            'flex-end' => 'Bottom',
                            'space-between' => 'Space Between',
                            'space-around' => 'Space Around',
                        ])
                        ->default('flex-start'),
                    \Filament\Forms\Components\Select::make('props.alignItems')
                        ->label('Horizontal Alignment')
                        ->options([
                            'flex-start' => 'Left',
                            'center' => 'Center',
                            'flex-end' => 'Right',
                            'stretch' => 'Stretch',
                        ])
                        ->default('stretch'),
                ]),
        ];
    }

    protected function renderDefault(array $props, array $children, array $styles, bool $isEditor): string
    {
        $childrenHtml = implode('', $children);
        $styleAttr = $this->buildColumnStyleAttribute($props, $styles);

        $classes = $isEditor ? 've-column' : '';

        return sprintf(
            '<div class="%s" data-block-type="%s" style="%s">%s</div>',
            e($classes),
            e($this->type),
            e($styleAttr),
            $childrenHtml
        );
    }

    protected function buildColumnStyleAttribute(array $props, array $styles): string
    {
        $css = [];

        // Grid placement
        $span = $props['span'] ?? 1;
        if ($span > 1) {
            $css[] = "grid-column: span {$span}";
        }

        if (isset($props['rowSpan']) && $props['rowSpan'] > 1) {
            $css[] = "grid-row: span {$props['rowSpan']}";
        }

        // Flex layout for children
        $css[] = 'display: ' . ($props['display'] ?? 'flex');
        $css[] = 'flex-direction: ' . ($props['flexDirection'] ?? 'column');
        $css[] = 'justify-content: ' . ($props['justifyContent'] ?? 'flex-start');
        $css[] = 'align-items: ' . ($props['alignItems'] ?? 'stretch');

        if (isset($props['gap']) && $props['gap'] !== '') {
            $css[] = "gap: {$props['gap']}";
        }

        // Add base styles
        $baseStyles = $this->buildStyleAttribute($styles);
        if ($baseStyles) {
            $css[] = $baseStyles;
        }

        return implode('; ', $css);
    }
}
