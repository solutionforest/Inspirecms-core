<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Blocks\Types;

use SolutionForest\InspireCmsVisualEditor\Enums\BlockCategory;

class GridBlock extends AbstractContainerBlock
{
    protected string $type = 'grid';

    protected BlockCategory $category = BlockCategory::LAYOUT;

    protected string $label = 'Grid';

    protected string $icon = 'heroicon-o-squares-2x2';

    protected string $description = 'A CSS Grid layout for creating complex layouts';

    protected string $childrenLayout = 'grid';

    protected array $allowedChildren = ['column', 'container', 'section'];

    public function getDefaultProps(): array
    {
        return [
            'display' => 'grid',
            'columns' => 2,
            'columnTemplate' => '',
            'rows' => 'auto',
            'rowTemplate' => '',
            'gap' => '24px',
            'columnGap' => '',
            'rowGap' => '',
        ];
    }

    public function getSettingsSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Grid Settings')
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('props.columns')
                                ->label('Columns')
                                ->numeric()
                                ->default(2)
                                ->minValue(1)
                                ->maxValue(12)
                                ->helperText('Number of columns (1-12)'),
                            \Filament\Forms\Components\TextInput::make('props.rows')
                                ->label('Rows')
                                ->placeholder('auto')
                                ->helperText('Number of rows or "auto"'),
                        ]),
                    \Filament\Forms\Components\TextInput::make('props.columnTemplate')
                        ->label('Column Template')
                        ->placeholder('1fr 1fr')
                        ->helperText('Custom grid-template-columns (overrides columns)'),
                    \Filament\Forms\Components\TextInput::make('props.rowTemplate')
                        ->label('Row Template')
                        ->placeholder('auto')
                        ->helperText('Custom grid-template-rows'),
                    \Filament\Forms\Components\Grid::make(3)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('props.gap')
                                ->label('Gap')
                                ->placeholder('24px')
                                ->helperText('Space between all items'),
                            \Filament\Forms\Components\TextInput::make('props.columnGap')
                                ->label('Column Gap')
                                ->placeholder('')
                                ->helperText('Override horizontal gap'),
                            \Filament\Forms\Components\TextInput::make('props.rowGap')
                                ->label('Row Gap')
                                ->placeholder('')
                                ->helperText('Override vertical gap'),
                        ]),
                ]),
            \Filament\Forms\Components\Section::make('Responsive')
                ->collapsed()
                ->schema([
                    \Filament\Forms\Components\TextInput::make('props.columnsMobile')
                        ->label('Mobile Columns')
                        ->numeric()
                        ->placeholder('1'),
                    \Filament\Forms\Components\TextInput::make('props.columnsTablet')
                        ->label('Tablet Columns')
                        ->numeric()
                        ->placeholder('2'),
                ]),
        ];
    }

    protected function renderDefault(array $props, array $children, array $styles, bool $isEditor): string
    {
        $childrenHtml = implode('', $children);
        $styleAttr = $this->buildGridStyleAttribute($props, $styles);

        $classes = $isEditor ? 've-grid' : '';

        return sprintf(
            '<div class="%s" data-block-type="%s" style="%s">%s</div>',
            e($classes),
            e($this->type),
            e($styleAttr),
            $childrenHtml
        );
    }

    protected function buildGridStyleAttribute(array $props, array $styles): string
    {
        $css = [];

        $css[] = 'display: grid';

        // Column template
        if (! empty($props['columnTemplate'])) {
            $css[] = "grid-template-columns: {$props['columnTemplate']}";
        } else {
            $columns = $props['columns'] ?? 2;
            $css[] = "grid-template-columns: repeat({$columns}, 1fr)";
        }

        // Row template
        if (! empty($props['rowTemplate'])) {
            $css[] = "grid-template-rows: {$props['rowTemplate']}";
        }

        // Gap
        if (! empty($props['columnGap']) || ! empty($props['rowGap'])) {
            if (! empty($props['columnGap'])) {
                $css[] = "column-gap: {$props['columnGap']}";
            }
            if (! empty($props['rowGap'])) {
                $css[] = "row-gap: {$props['rowGap']}";
            }
        } elseif (! empty($props['gap'])) {
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
