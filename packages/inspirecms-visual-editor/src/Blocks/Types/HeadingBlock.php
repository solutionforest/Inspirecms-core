<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Blocks\Types;

use SolutionForest\InspireCmsVisualEditor\Enums\BlockCategory;

class HeadingBlock extends AbstractBlock
{
    protected string $type = 'heading';

    protected BlockCategory $category = BlockCategory::BASIC;

    protected string $label = 'Heading';

    protected string $icon = 'heroicon-o-h1';

    protected string $description = 'A heading text element (H1-H6)';

    public function getDefaultProps(): array
    {
        return [
            'text' => 'Heading',
            'level' => 2,
            'alignment' => 'left',
        ];
    }

    public function getSettingsSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Heading Settings')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('props.text')
                        ->label('Text')
                        ->required()
                        ->placeholder('Enter heading text'),
                    \Filament\Forms\Components\Select::make('props.level')
                        ->label('Heading Level')
                        ->options([
                            1 => 'H1 - Main Heading',
                            2 => 'H2 - Section Heading',
                            3 => 'H3 - Subsection',
                            4 => 'H4 - Small Heading',
                            5 => 'H5 - Minor Heading',
                            6 => 'H6 - Smallest Heading',
                        ])
                        ->default(2),
                    \Filament\Forms\Components\Select::make('props.alignment')
                        ->label('Alignment')
                        ->options([
                            'left' => 'Left',
                            'center' => 'Center',
                            'right' => 'Right',
                        ])
                        ->default('left'),
                ]),
            \Filament\Forms\Components\Section::make('Advanced')
                ->collapsed()
                ->schema([
                    \Filament\Forms\Components\TextInput::make('props.htmlId')
                        ->label('HTML ID')
                        ->placeholder('my-heading')
                        ->helperText('For anchor links'),
                    \Filament\Forms\Components\TextInput::make('props.cssClass')
                        ->label('CSS Class')
                        ->placeholder('custom-class'),
                ]),
        ];
    }

    public function getStyleSchema(): array
    {
        return $this->getBaseStyleSchema();
    }

    protected function renderDefault(array $props, array $children, array $styles, bool $isEditor): string
    {
        $level = $props['level'] ?? 2;
        $text = e($props['text'] ?? 'Heading');
        $alignment = $props['alignment'] ?? 'left';

        $id = isset($props['htmlId']) && $props['htmlId'] !== '' ? ' id="' . e($props['htmlId']) . '"' : '';
        $cssClass = isset($props['cssClass']) && $props['cssClass'] !== '' ? ' ' . e($props['cssClass']) : '';

        $styleAttr = $this->buildStyleAttribute($styles);
        $styleAttr .= "; text-align: {$alignment}";

        $editorClass = $isEditor ? 've-heading' : '';

        return sprintf(
            '<h%d class="%s%s" data-block-type="%s"%s style="%s">%s</h%d>',
            $level,
            e($editorClass),
            $cssClass,
            e($this->type),
            $id,
            e($styleAttr),
            $text,
            $level
        );
    }

    public function validateProps(array $props): array
    {
        $errors = [];

        if (empty($props['text'])) {
            $errors['text'] = 'Heading text is required';
        }

        $level = $props['level'] ?? 2;
        if ($level < 1 || $level > 6) {
            $errors['level'] = 'Heading level must be between 1 and 6';
        }

        return $errors;
    }
}
