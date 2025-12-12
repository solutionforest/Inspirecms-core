<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\Blocks\Types;

use SolutionForest\InspireCms\VisualEditor\Enums\BlockCategory;

class ContainerBlock extends AbstractContainerBlock
{
    protected string $type = 'container';

    protected BlockCategory $category = BlockCategory::LAYOUT;

    protected string $label = 'Container';

    protected string $icon = 'heroicon-o-rectangle-stack';

    protected string $description = 'A flexible container for grouping and laying out other blocks';

    public function getDefaultProps(): array
    {
        return [
            'display' => 'flex',
            'flexDirection' => 'column',
            'flexWrap' => 'nowrap',
            'justifyContent' => 'flex-start',
            'alignItems' => 'stretch',
            'gap' => '16px',
            'maxWidth' => '1200px',
            'centered' => true,
        ];
    }

    public function getSettingsSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Container Settings')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('props.maxWidth')
                        ->label('Max Width')
                        ->placeholder('1200px')
                        ->helperText('Maximum width of the container'),
                    \Filament\Forms\Components\Toggle::make('props.centered')
                        ->label('Center Container')
                        ->default(true)
                        ->helperText('Center the container horizontally'),
                    \Filament\Forms\Components\TextInput::make('props.minHeight')
                        ->label('Min Height')
                        ->placeholder('auto')
                        ->helperText('Minimum height of the container'),
                ]),
        ];
    }
}
