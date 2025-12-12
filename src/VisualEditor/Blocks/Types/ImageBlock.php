<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\Blocks\Types;

use SolutionForest\InspireCms\VisualEditor\Enums\BlockCategory;

class ImageBlock extends AbstractBlock
{
    protected string $type = 'image';

    protected BlockCategory $category = BlockCategory::MEDIA;

    protected string $label = 'Image';

    protected string $icon = 'heroicon-o-photo';

    protected string $description = 'An image element with optional link and caption';

    public function getDefaultProps(): array
    {
        return [
            'src' => '',
            'alt' => '',
            'caption' => '',
            'link' => '',
            'linkTarget' => '_self',
            'aspectRatio' => 'auto',
            'objectFit' => 'cover',
            'alignment' => 'center',
            'rounded' => false,
            'shadow' => false,
        ];
    }

    public function getSettingsSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Image Settings')
                ->schema([
                    \Filament\Forms\Components\FileUpload::make('props.src')
                        ->label('Image')
                        ->image()
                        ->required()
                        ->disk('public')
                        ->directory('visual-editor')
                        ->imageEditor()
                        ->columnSpanFull(),
                    \Filament\Forms\Components\TextInput::make('props.alt')
                        ->label('Alt Text')
                        ->required()
                        ->placeholder('Describe the image')
                        ->helperText('Important for accessibility and SEO'),
                    \Filament\Forms\Components\TextInput::make('props.caption')
                        ->label('Caption')
                        ->placeholder('Optional image caption'),
                ]),
            \Filament\Forms\Components\Section::make('Link')
                ->collapsed()
                ->schema([
                    \Filament\Forms\Components\TextInput::make('props.link')
                        ->label('Link URL')
                        ->placeholder('https://example.com')
                        ->url(),
                    \Filament\Forms\Components\Select::make('props.linkTarget')
                        ->label('Open In')
                        ->options([
                            '_self' => 'Same Window',
                            '_blank' => 'New Tab',
                        ])
                        ->default('_self'),
                ]),
            \Filament\Forms\Components\Section::make('Display Options')
                ->schema([
                    \Filament\Forms\Components\Select::make('props.aspectRatio')
                        ->label('Aspect Ratio')
                        ->options([
                            'auto' => 'Auto (Original)',
                            '1/1' => 'Square (1:1)',
                            '16/9' => 'Widescreen (16:9)',
                            '4/3' => 'Standard (4:3)',
                            '3/2' => 'Photo (3:2)',
                            '21/9' => 'Ultra Wide (21:9)',
                        ])
                        ->default('auto'),
                    \Filament\Forms\Components\Select::make('props.objectFit')
                        ->label('Object Fit')
                        ->options([
                            'contain' => 'Contain',
                            'cover' => 'Cover',
                            'fill' => 'Fill',
                            'none' => 'None',
                        ])
                        ->default('cover'),
                    \Filament\Forms\Components\Select::make('props.alignment')
                        ->label('Alignment')
                        ->options([
                            'left' => 'Left',
                            'center' => 'Center',
                            'right' => 'Right',
                        ])
                        ->default('center'),
                    \Filament\Forms\Components\Toggle::make('props.rounded')
                        ->label('Rounded Corners')
                        ->default(false),
                    \Filament\Forms\Components\Toggle::make('props.shadow')
                        ->label('Drop Shadow')
                        ->default(false),
                ]),
            \Filament\Forms\Components\Section::make('Size')
                ->collapsed()
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('props.width')
                                ->label('Width')
                                ->placeholder('100%'),
                            \Filament\Forms\Components\TextInput::make('props.maxWidth')
                                ->label('Max Width')
                                ->placeholder('none'),
                            \Filament\Forms\Components\TextInput::make('props.height')
                                ->label('Height')
                                ->placeholder('auto'),
                            \Filament\Forms\Components\TextInput::make('props.maxHeight')
                                ->label('Max Height')
                                ->placeholder('none'),
                        ]),
                ]),
        ];
    }

    public function getStyleSchema(): array
    {
        return $this->getBaseStyleSchema();
    }

    protected function renderDefault(array $props, array $children, array $styles, bool $isEditor): string
    {
        $src = $props['src'] ?? '';
        $alt = e($props['alt'] ?? '');
        $caption = $props['caption'] ?? '';
        $link = $props['link'] ?? '';
        $linkTarget = $props['linkTarget'] ?? '_self';
        $aspectRatio = $props['aspectRatio'] ?? 'auto';
        $objectFit = $props['objectFit'] ?? 'cover';
        $alignment = $props['alignment'] ?? 'center';
        $rounded = $props['rounded'] ?? false;
        $shadow = $props['shadow'] ?? false;

        $editorClass = $isEditor ? 've-image' : '';

        // Build image styles
        $imgStyles = [];
        $imgStyles[] = 'display: block';
        $imgStyles[] = 'max-width: 100%';
        $imgStyles[] = 'height: auto';
        $imgStyles[] = "object-fit: {$objectFit}";

        if (isset($props['width'])) {
            $imgStyles[] = "width: {$props['width']}";
        }
        if (isset($props['maxWidth'])) {
            $imgStyles[] = "max-width: {$props['maxWidth']}";
        }
        if (isset($props['height'])) {
            $imgStyles[] = "height: {$props['height']}";
        }
        if (isset($props['maxHeight'])) {
            $imgStyles[] = "max-height: {$props['maxHeight']}";
        }

        if ($rounded) {
            $imgStyles[] = 'border-radius: 8px';
        }
        if ($shadow) {
            $imgStyles[] = 'box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
        }

        $imgStyle = implode('; ', $imgStyles);

        // Build wrapper styles
        $wrapperStyles = [];
        $wrapperStyles[] = "text-align: {$alignment}";

        if ($aspectRatio !== 'auto') {
            $wrapperStyles[] = "aspect-ratio: {$aspectRatio}";
            $wrapperStyles[] = 'overflow: hidden';
        }

        $wrapperStyle = implode('; ', $wrapperStyles);

        // Build base styles
        $baseStyles = $this->buildStyleAttribute($styles);
        if ($baseStyles) {
            $wrapperStyle .= "; {$baseStyles}";
        }

        // Build image element
        $imgHtml = sprintf(
            '<img src="%s" alt="%s" style="%s" loading="lazy" />',
            e($src),
            $alt,
            e($imgStyle)
        );

        // Wrap in link if provided
        if (! empty($link)) {
            $imgHtml = sprintf(
                '<a href="%s" target="%s">%s</a>',
                e($link),
                e($linkTarget),
                $imgHtml
            );
        }

        // Build figure with optional caption
        if (! empty($caption)) {
            return sprintf(
                '<figure class="%s" data-block-type="%s" style="%s">%s<figcaption style="text-align: center; margin-top: 8px; color: #6b7280; font-size: 14px;">%s</figcaption></figure>',
                e($editorClass),
                e($this->type),
                e($wrapperStyle),
                $imgHtml,
                e($caption)
            );
        }

        return sprintf(
            '<div class="%s" data-block-type="%s" style="%s">%s</div>',
            e($editorClass),
            e($this->type),
            e($wrapperStyle),
            $imgHtml
        );
    }

    public function validateProps(array $props): array
    {
        $errors = [];

        if (empty($props['src'])) {
            $errors['src'] = 'Image source is required';
        }

        if (empty($props['alt'])) {
            $errors['alt'] = 'Alt text is required for accessibility';
        }

        return $errors;
    }
}
