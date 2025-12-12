<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\Blocks\Types;

use SolutionForest\InspireCms\VisualEditor\Enums\BlockCategory;

class TextBlock extends AbstractBlock
{
    protected string $type = 'text';

    protected BlockCategory $category = BlockCategory::BASIC;

    protected string $label = 'Text';

    protected string $icon = 'heroicon-o-document-text';

    protected string $description = 'A paragraph or rich text element';

    public function getDefaultProps(): array
    {
        return [
            'content' => 'Enter your text here...',
            'format' => 'paragraph',
            'alignment' => 'left',
        ];
    }

    public function getSettingsSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Text Settings')
                ->schema([
                    \Filament\Forms\Components\RichEditor::make('props.content')
                        ->label('Content')
                        ->required()
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'strike',
                            'link',
                            'bulletList',
                            'orderedList',
                            'blockquote',
                        ])
                        ->columnSpanFull(),
                    \Filament\Forms\Components\Select::make('props.format')
                        ->label('Format')
                        ->options([
                            'paragraph' => 'Paragraph',
                            'lead' => 'Lead Text (Larger)',
                            'small' => 'Small Text',
                            'blockquote' => 'Blockquote',
                        ])
                        ->default('paragraph'),
                    \Filament\Forms\Components\Select::make('props.alignment')
                        ->label('Alignment')
                        ->options([
                            'left' => 'Left',
                            'center' => 'Center',
                            'right' => 'Right',
                            'justify' => 'Justify',
                        ])
                        ->default('left'),
                ]),
            \Filament\Forms\Components\Section::make('Advanced')
                ->collapsed()
                ->schema([
                    \Filament\Forms\Components\TextInput::make('props.cssClass')
                        ->label('CSS Class')
                        ->placeholder('custom-class'),
                    \Filament\Forms\Components\TextInput::make('props.lineHeight')
                        ->label('Line Height')
                        ->placeholder('1.6'),
                ]),
        ];
    }

    public function getStyleSchema(): array
    {
        return $this->getBaseStyleSchema();
    }

    protected function renderDefault(array $props, array $children, array $styles, bool $isEditor): string
    {
        $content = $props['content'] ?? 'Enter your text here...';
        $format = $props['format'] ?? 'paragraph';
        $alignment = $props['alignment'] ?? 'left';

        $cssClass = isset($props['cssClass']) && $props['cssClass'] !== '' ? ' ' . e($props['cssClass']) : '';

        $styleAttr = $this->buildStyleAttribute($styles);
        $styleAttr .= "; text-align: {$alignment}";

        if (isset($props['lineHeight']) && $props['lineHeight'] !== '') {
            $styleAttr .= "; line-height: {$props['lineHeight']}";
        }

        // Add format-specific styles
        $formatStyles = match ($format) {
            'lead' => 'font-size: 1.25em',
            'small' => 'font-size: 0.875em',
            'blockquote' => 'border-left: 4px solid #e5e7eb; padding-left: 1em; font-style: italic',
            default => '',
        };

        if ($formatStyles) {
            $styleAttr .= "; {$formatStyles}";
        }

        $editorClass = $isEditor ? 've-text' : '';
        $tag = $format === 'blockquote' ? 'blockquote' : 'div';

        return sprintf(
            '<%s class="%s%s" data-block-type="%s" style="%s">%s</%s>',
            $tag,
            e($editorClass),
            $cssClass,
            e($this->type),
            e($styleAttr),
            $content, // Already HTML from rich editor
            $tag
        );
    }

    public function validateProps(array $props): array
    {
        $errors = [];

        if (empty($props['content'])) {
            $errors['content'] = 'Text content is required';
        }

        return $errors;
    }
}
