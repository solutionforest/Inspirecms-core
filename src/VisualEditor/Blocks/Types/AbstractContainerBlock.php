<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\Blocks\Types;

use SolutionForest\InspireCms\VisualEditor\Blocks\Contracts\ContainerBlockInterface;

abstract class AbstractContainerBlock extends AbstractBlock implements ContainerBlockInterface
{
    protected bool $isContainer = true;

    protected string $childrenLayout = 'flex';

    protected array $childrenLayoutOptions = [
        'direction' => 'column',
        'gap' => '0',
    ];

    public function getChildrenLayout(): string
    {
        return $this->childrenLayout;
    }

    public function getChildrenLayoutOptions(): array
    {
        return $this->childrenLayoutOptions;
    }

    public function wrapChildren(string $childrenHtml, array $props): string
    {
        return $childrenHtml;
    }

    protected function getLayoutSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Layout')
                ->schema([
                    \Filament\Forms\Components\Select::make('props.display')
                        ->label('Display')
                        ->options([
                            'block' => 'Block',
                            'flex' => 'Flex',
                            'grid' => 'Grid',
                            'inline' => 'Inline',
                            'inline-block' => 'Inline Block',
                        ])
                        ->default('flex')
                        ->reactive(),
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\Select::make('props.flexDirection')
                                ->label('Direction')
                                ->options([
                                    'row' => 'Row',
                                    'row-reverse' => 'Row Reverse',
                                    'column' => 'Column',
                                    'column-reverse' => 'Column Reverse',
                                ])
                                ->default('column')
                                ->visible(fn (callable $get) => in_array($get('props.display'), ['flex', null])),
                            \Filament\Forms\Components\Select::make('props.flexWrap')
                                ->label('Wrap')
                                ->options([
                                    'nowrap' => 'No Wrap',
                                    'wrap' => 'Wrap',
                                    'wrap-reverse' => 'Wrap Reverse',
                                ])
                                ->default('nowrap')
                                ->visible(fn (callable $get) => in_array($get('props.display'), ['flex', null])),
                        ]),
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\Select::make('props.justifyContent')
                                ->label('Justify Content')
                                ->options([
                                    'flex-start' => 'Start',
                                    'flex-end' => 'End',
                                    'center' => 'Center',
                                    'space-between' => 'Space Between',
                                    'space-around' => 'Space Around',
                                    'space-evenly' => 'Space Evenly',
                                ])
                                ->default('flex-start')
                                ->visible(fn (callable $get) => in_array($get('props.display'), ['flex', null])),
                            \Filament\Forms\Components\Select::make('props.alignItems')
                                ->label('Align Items')
                                ->options([
                                    'flex-start' => 'Start',
                                    'flex-end' => 'End',
                                    'center' => 'Center',
                                    'stretch' => 'Stretch',
                                    'baseline' => 'Baseline',
                                ])
                                ->default('stretch')
                                ->visible(fn (callable $get) => in_array($get('props.display'), ['flex', null])),
                        ]),
                    \Filament\Forms\Components\TextInput::make('props.gap')
                        ->label('Gap')
                        ->placeholder('0')
                        ->helperText('Space between children (e.g., 16px, 1rem)'),
                ]),
        ];
    }

    public function getStyleSchema(): array
    {
        return array_merge(
            $this->getLayoutSchema(),
            $this->getBaseStyleSchema()
        );
    }

    protected function renderDefault(array $props, array $children, array $styles, bool $isEditor): string
    {
        $childrenHtml = implode('', $children);
        $styleAttr = $this->buildContainerStyleAttribute($props, $styles);

        $classes = $isEditor ? 've-container' : '';

        return sprintf(
            '<div class="%s" data-block-type="%s" style="%s">%s</div>',
            e($classes),
            e($this->type),
            e($styleAttr),
            $childrenHtml
        );
    }

    protected function buildContainerStyleAttribute(array $props, array $styles): string
    {
        $css = [];

        // Layout properties
        $display = $props['display'] ?? 'flex';
        $css[] = "display: {$display}";

        if ($display === 'flex') {
            $css[] = 'flex-direction: ' . ($props['flexDirection'] ?? 'column');
            $css[] = 'flex-wrap: ' . ($props['flexWrap'] ?? 'nowrap');
            $css[] = 'justify-content: ' . ($props['justifyContent'] ?? 'flex-start');
            $css[] = 'align-items: ' . ($props['alignItems'] ?? 'stretch');
        }

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
