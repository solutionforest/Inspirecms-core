<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\Blocks\Types;

use SolutionForest\InspireCms\VisualEditor\Enums\BlockCategory;

class ButtonBlock extends AbstractBlock
{
    protected string $type = 'button';

    protected BlockCategory $category = BlockCategory::BASIC;

    protected string $label = 'Button';

    protected string $icon = 'heroicon-o-cursor-arrow-ripple';

    protected string $description = 'A clickable button or link element';

    public function getDefaultProps(): array
    {
        return [
            'text' => 'Click me',
            'url' => '#',
            'target' => '_self',
            'variant' => 'primary',
            'size' => 'medium',
            'fullWidth' => false,
            'icon' => null,
            'iconPosition' => 'left',
        ];
    }

    public function getSettingsSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Button Settings')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('props.text')
                        ->label('Button Text')
                        ->required()
                        ->placeholder('Click me'),
                    \Filament\Forms\Components\TextInput::make('props.url')
                        ->label('Link URL')
                        ->placeholder('https://example.com')
                        ->url()
                        ->helperText('Leave empty for no link'),
                    \Filament\Forms\Components\Select::make('props.target')
                        ->label('Open In')
                        ->options([
                            '_self' => 'Same Window',
                            '_blank' => 'New Tab',
                        ])
                        ->default('_self'),
                ]),
            \Filament\Forms\Components\Section::make('Appearance')
                ->schema([
                    \Filament\Forms\Components\Select::make('props.variant')
                        ->label('Style')
                        ->options([
                            'primary' => 'Primary',
                            'secondary' => 'Secondary',
                            'outline' => 'Outline',
                            'ghost' => 'Ghost',
                            'link' => 'Link Style',
                        ])
                        ->default('primary'),
                    \Filament\Forms\Components\Select::make('props.size')
                        ->label('Size')
                        ->options([
                            'small' => 'Small',
                            'medium' => 'Medium',
                            'large' => 'Large',
                        ])
                        ->default('medium'),
                    \Filament\Forms\Components\Toggle::make('props.fullWidth')
                        ->label('Full Width')
                        ->default(false),
                ]),
            \Filament\Forms\Components\Section::make('Icon')
                ->collapsed()
                ->schema([
                    \Filament\Forms\Components\TextInput::make('props.icon')
                        ->label('Icon')
                        ->placeholder('heroicon-o-arrow-right')
                        ->helperText('Heroicon name'),
                    \Filament\Forms\Components\Select::make('props.iconPosition')
                        ->label('Icon Position')
                        ->options([
                            'left' => 'Left',
                            'right' => 'Right',
                        ])
                        ->default('left'),
                ]),
            \Filament\Forms\Components\Section::make('Advanced')
                ->collapsed()
                ->schema([
                    \Filament\Forms\Components\TextInput::make('props.cssClass')
                        ->label('CSS Class')
                        ->placeholder('custom-class'),
                    \Filament\Forms\Components\TextInput::make('props.ariaLabel')
                        ->label('Aria Label')
                        ->placeholder('Accessible label'),
                ]),
        ];
    }

    public function getStyleSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Button Styles')
                ->schema([
                    \Filament\Forms\Components\ColorPicker::make('styles.backgroundColor')
                        ->label('Background Color'),
                    \Filament\Forms\Components\ColorPicker::make('styles.color')
                        ->label('Text Color'),
                    \Filament\Forms\Components\ColorPicker::make('styles.hoverBackgroundColor')
                        ->label('Hover Background'),
                    \Filament\Forms\Components\ColorPicker::make('styles.hoverColor')
                        ->label('Hover Text Color'),
                    \Filament\Forms\Components\TextInput::make('styles.borderRadius')
                        ->label('Border Radius')
                        ->placeholder('8px'),
                    \Filament\Forms\Components\TextInput::make('styles.padding')
                        ->label('Padding')
                        ->placeholder('12px 24px'),
                ]),
        ];
    }

    protected function renderDefault(array $props, array $children, array $styles, bool $isEditor): string
    {
        $text = e($props['text'] ?? 'Click me');
        $url = $props['url'] ?? '#';
        $target = $props['target'] ?? '_self';
        $variant = $props['variant'] ?? 'primary';
        $size = $props['size'] ?? 'medium';
        $fullWidth = $props['fullWidth'] ?? false;

        $cssClass = isset($props['cssClass']) && $props['cssClass'] !== '' ? ' ' . e($props['cssClass']) : '';
        $ariaLabel = isset($props['ariaLabel']) && $props['ariaLabel'] !== '' ? ' aria-label="' . e($props['ariaLabel']) . '"' : '';

        $editorClass = $isEditor ? 've-button' : '';

        // Build variant styles
        $variantStyles = $this->getVariantStyles($variant);
        $sizeStyles = $this->getSizeStyles($size);

        $styleAttr = $this->buildStyleAttribute($styles);
        $styleAttr .= "; {$variantStyles}; {$sizeStyles}";
        $styleAttr .= '; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; cursor: pointer; transition: all 0.2s';

        if ($fullWidth) {
            $styleAttr .= '; width: 100%';
        }

        // Determine tag
        $tag = ! empty($url) && $url !== '#' ? 'a' : 'button';
        $hrefAttr = $tag === 'a' ? ' href="' . e($url) . '" target="' . e($target) . '"' : ' type="button"';

        // Icon handling
        $iconHtml = '';
        if (! empty($props['icon'])) {
            $iconPosition = $props['iconPosition'] ?? 'left';
            $iconHtml = sprintf('<span class="ve-button-icon">%s</span>', e($props['icon']));
        }

        $content = $text;
        if ($iconHtml && ($props['iconPosition'] ?? 'left') === 'left') {
            $content = $iconHtml . ' ' . $text;
        } elseif ($iconHtml) {
            $content = $text . ' ' . $iconHtml;
        }

        return sprintf(
            '<%s class="%s%s" data-block-type="%s"%s%s style="%s">%s</%s>',
            $tag,
            e($editorClass),
            $cssClass,
            e($this->type),
            $hrefAttr,
            $ariaLabel,
            e($styleAttr),
            $content,
            $tag
        );
    }

    protected function getVariantStyles(string $variant): string
    {
        return match ($variant) {
            'primary' => 'background-color: #3b82f6; color: #ffffff; border: none',
            'secondary' => 'background-color: #6b7280; color: #ffffff; border: none',
            'outline' => 'background-color: transparent; color: #3b82f6; border: 2px solid #3b82f6',
            'ghost' => 'background-color: transparent; color: #3b82f6; border: none',
            'link' => 'background-color: transparent; color: #3b82f6; border: none; padding: 0',
            default => 'background-color: #3b82f6; color: #ffffff; border: none',
        };
    }

    protected function getSizeStyles(string $size): string
    {
        return match ($size) {
            'small' => 'padding: 8px 16px; font-size: 14px; border-radius: 6px',
            'medium' => 'padding: 12px 24px; font-size: 16px; border-radius: 8px',
            'large' => 'padding: 16px 32px; font-size: 18px; border-radius: 10px',
            default => 'padding: 12px 24px; font-size: 16px; border-radius: 8px',
        };
    }

    public function validateProps(array $props): array
    {
        $errors = [];

        if (empty($props['text'])) {
            $errors['text'] = 'Button text is required';
        }

        return $errors;
    }
}
