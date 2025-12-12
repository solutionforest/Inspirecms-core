<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\Blocks\Types;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use SolutionForest\InspireCms\VisualEditor\Blocks\Contracts\BlockInterface;
use SolutionForest\InspireCms\VisualEditor\Enums\BlockCategory;

abstract class AbstractBlock implements BlockInterface
{
    /**
     * The block type identifier.
     */
    protected string $type;

    /**
     * The block category.
     */
    protected BlockCategory $category = BlockCategory::BASIC;

    /**
     * The block label.
     */
    protected string $label;

    /**
     * The block icon.
     */
    protected string $icon = 'heroicon-o-cube';

    /**
     * The block description.
     */
    protected string $description = '';

    /**
     * Whether the block can contain children.
     */
    protected bool $isContainer = false;

    /**
     * Allowed child block types (empty = all allowed).
     */
    protected array $allowedChildren = [];

    /**
     * Maximum number of children (null = unlimited).
     */
    protected ?int $maxChildren = null;

    /**
     * The preview image path.
     */
    protected ?string $previewImage = null;

    public function getType(): string
    {
        return $this->type;
    }

    public function getCategory(): string
    {
        return $this->category->value;
    }

    public function getLabel(): string
    {
        return $this->label ?? ucfirst($this->type);
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isContainer(): bool
    {
        return $this->isContainer;
    }

    public function getAllowedChildren(): array
    {
        return $this->allowedChildren;
    }

    public function getMaxChildren(): ?int
    {
        return $this->maxChildren;
    }

    public function getPreviewImage(): ?string
    {
        return $this->previewImage;
    }

    /**
     * Get default style schema shared by all blocks.
     */
    protected function getBaseStyleSchema(): array
    {
        return [
            \Filament\Forms\Components\Tabs::make('Styles')
                ->tabs([
                    \Filament\Forms\Components\Tabs\Tab::make('Spacing')
                        ->icon('heroicon-o-arrows-pointing-out')
                        ->schema($this->getSpacingSchema()),
                    \Filament\Forms\Components\Tabs\Tab::make('Size')
                        ->icon('heroicon-o-arrows-pointing-in')
                        ->schema($this->getSizeSchema()),
                    \Filament\Forms\Components\Tabs\Tab::make('Typography')
                        ->icon('heroicon-o-language')
                        ->schema($this->getTypographySchema()),
                    \Filament\Forms\Components\Tabs\Tab::make('Background')
                        ->icon('heroicon-o-paint-brush')
                        ->schema($this->getBackgroundSchema()),
                    \Filament\Forms\Components\Tabs\Tab::make('Border')
                        ->icon('heroicon-o-stop')
                        ->schema($this->getBorderSchema()),
                ]),
        ];
    }

    protected function getSpacingSchema(): array
    {
        return [
            \Filament\Forms\Components\Grid::make(4)
                ->schema([
                    \Filament\Forms\Components\TextInput::make('styles.padding.top')
                        ->label('Padding Top')
                        ->placeholder('0'),
                    \Filament\Forms\Components\TextInput::make('styles.padding.right')
                        ->label('Right')
                        ->placeholder('0'),
                    \Filament\Forms\Components\TextInput::make('styles.padding.bottom')
                        ->label('Bottom')
                        ->placeholder('0'),
                    \Filament\Forms\Components\TextInput::make('styles.padding.left')
                        ->label('Left')
                        ->placeholder('0'),
                ]),
            \Filament\Forms\Components\Grid::make(4)
                ->schema([
                    \Filament\Forms\Components\TextInput::make('styles.margin.top')
                        ->label('Margin Top')
                        ->placeholder('auto'),
                    \Filament\Forms\Components\TextInput::make('styles.margin.right')
                        ->label('Right')
                        ->placeholder('auto'),
                    \Filament\Forms\Components\TextInput::make('styles.margin.bottom')
                        ->label('Bottom')
                        ->placeholder('auto'),
                    \Filament\Forms\Components\TextInput::make('styles.margin.left')
                        ->label('Left')
                        ->placeholder('auto'),
                ]),
        ];
    }

    protected function getSizeSchema(): array
    {
        return [
            \Filament\Forms\Components\Grid::make(2)
                ->schema([
                    \Filament\Forms\Components\TextInput::make('styles.width')
                        ->label('Width')
                        ->placeholder('auto'),
                    \Filament\Forms\Components\TextInput::make('styles.height')
                        ->label('Height')
                        ->placeholder('auto'),
                    \Filament\Forms\Components\TextInput::make('styles.minWidth')
                        ->label('Min Width')
                        ->placeholder('0'),
                    \Filament\Forms\Components\TextInput::make('styles.minHeight')
                        ->label('Min Height')
                        ->placeholder('0'),
                    \Filament\Forms\Components\TextInput::make('styles.maxWidth')
                        ->label('Max Width')
                        ->placeholder('none'),
                    \Filament\Forms\Components\TextInput::make('styles.maxHeight')
                        ->label('Max Height')
                        ->placeholder('none'),
                ]),
            \Filament\Forms\Components\Select::make('styles.overflow')
                ->label('Overflow')
                ->options([
                    'visible' => 'Visible',
                    'hidden' => 'Hidden',
                    'scroll' => 'Scroll',
                    'auto' => 'Auto',
                ]),
        ];
    }

    protected function getTypographySchema(): array
    {
        return [
            \Filament\Forms\Components\Select::make('styles.fontFamily')
                ->label('Font Family')
                ->options([
                    'inherit' => 'Inherit',
                    'sans-serif' => 'Sans Serif',
                    'serif' => 'Serif',
                    'monospace' => 'Monospace',
                ]),
            \Filament\Forms\Components\Grid::make(2)
                ->schema([
                    \Filament\Forms\Components\TextInput::make('styles.fontSize')
                        ->label('Font Size')
                        ->placeholder('inherit'),
                    \Filament\Forms\Components\Select::make('styles.fontWeight')
                        ->label('Font Weight')
                        ->options([
                            'normal' => 'Normal',
                            'medium' => 'Medium',
                            'semibold' => 'Semi Bold',
                            'bold' => 'Bold',
                        ]),
                ]),
            \Filament\Forms\Components\ColorPicker::make('styles.color')
                ->label('Text Color'),
            \Filament\Forms\Components\Select::make('styles.textAlign')
                ->label('Text Align')
                ->options([
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right',
                    'justify' => 'Justify',
                ]),
        ];
    }

    protected function getBackgroundSchema(): array
    {
        return [
            \Filament\Forms\Components\ColorPicker::make('styles.backgroundColor')
                ->label('Background Color'),
            \Filament\Forms\Components\TextInput::make('styles.backgroundImage')
                ->label('Background Image URL')
                ->placeholder('url(...)'),
            \Filament\Forms\Components\Select::make('styles.backgroundSize')
                ->label('Background Size')
                ->options([
                    'auto' => 'Auto',
                    'cover' => 'Cover',
                    'contain' => 'Contain',
                ]),
            \Filament\Forms\Components\Select::make('styles.backgroundPosition')
                ->label('Background Position')
                ->options([
                    'center' => 'Center',
                    'top' => 'Top',
                    'bottom' => 'Bottom',
                    'left' => 'Left',
                    'right' => 'Right',
                ]),
        ];
    }

    protected function getBorderSchema(): array
    {
        return [
            \Filament\Forms\Components\Grid::make(2)
                ->schema([
                    \Filament\Forms\Components\TextInput::make('styles.borderWidth')
                        ->label('Border Width')
                        ->placeholder('0'),
                    \Filament\Forms\Components\Select::make('styles.borderStyle')
                        ->label('Border Style')
                        ->options([
                            'none' => 'None',
                            'solid' => 'Solid',
                            'dashed' => 'Dashed',
                            'dotted' => 'Dotted',
                        ]),
                ]),
            \Filament\Forms\Components\ColorPicker::make('styles.borderColor')
                ->label('Border Color'),
            \Filament\Forms\Components\TextInput::make('styles.borderRadius')
                ->label('Border Radius')
                ->placeholder('0'),
        ];
    }

    public function getStyleSchema(): array
    {
        return $this->getBaseStyleSchema();
    }

    public function render(array $props, array $children = [], array $styles = []): View | string
    {
        $viewName = $this->getViewName();

        if (ViewFacade::exists($viewName)) {
            return view($viewName, [
                'props' => $props,
                'children' => $children,
                'styles' => $styles,
                'block' => $this,
                'isEditor' => true,
            ]);
        }

        return $this->renderDefault($props, $children, $styles, true);
    }

    public function renderFrontend(array $props, array $children = [], array $styles = []): View | string
    {
        $viewName = $this->getFrontendViewName();

        if (ViewFacade::exists($viewName)) {
            return view($viewName, [
                'props' => $props,
                'children' => $children,
                'styles' => $styles,
                'block' => $this,
                'isEditor' => false,
            ]);
        }

        // Fall back to editor view
        return $this->render($props, $children, $styles);
    }

    /**
     * Get the view name for editor rendering.
     */
    protected function getViewName(): string
    {
        return 'inspirecms::visual-editor.blocks.' . $this->type;
    }

    /**
     * Get the view name for frontend rendering.
     */
    protected function getFrontendViewName(): string
    {
        return 'inspirecms::visual-editor.blocks.frontend.' . $this->type;
    }

    /**
     * Render a default representation if no view exists.
     */
    protected function renderDefault(array $props, array $children, array $styles, bool $isEditor): string
    {
        $childrenHtml = implode('', $children);
        $styleAttr = $this->buildStyleAttribute($styles);

        return sprintf(
            '<div data-block-type="%s" style="%s">%s</div>',
            e($this->type),
            e($styleAttr),
            $childrenHtml
        );
    }

    /**
     * Build a CSS style attribute from styles array.
     */
    protected function buildStyleAttribute(array $styles): string
    {
        $css = [];

        $styleMap = [
            'width' => 'width',
            'height' => 'height',
            'minWidth' => 'min-width',
            'minHeight' => 'min-height',
            'maxWidth' => 'max-width',
            'maxHeight' => 'max-height',
            'overflow' => 'overflow',
            'fontFamily' => 'font-family',
            'fontSize' => 'font-size',
            'fontWeight' => 'font-weight',
            'color' => 'color',
            'textAlign' => 'text-align',
            'backgroundColor' => 'background-color',
            'backgroundImage' => 'background-image',
            'backgroundSize' => 'background-size',
            'backgroundPosition' => 'background-position',
            'borderWidth' => 'border-width',
            'borderStyle' => 'border-style',
            'borderColor' => 'border-color',
            'borderRadius' => 'border-radius',
        ];

        foreach ($styleMap as $key => $cssProperty) {
            if (isset($styles[$key]) && $styles[$key] !== '') {
                $css[] = "{$cssProperty}: {$styles[$key]}";
            }
        }

        // Handle padding
        if (isset($styles['padding'])) {
            $padding = $styles['padding'];
            $css[] = sprintf(
                'padding: %s %s %s %s',
                $padding['top'] ?? '0',
                $padding['right'] ?? '0',
                $padding['bottom'] ?? '0',
                $padding['left'] ?? '0'
            );
        }

        // Handle margin
        if (isset($styles['margin'])) {
            $margin = $styles['margin'];
            $css[] = sprintf(
                'margin: %s %s %s %s',
                $margin['top'] ?? 'auto',
                $margin['right'] ?? 'auto',
                $margin['bottom'] ?? 'auto',
                $margin['left'] ?? 'auto'
            );
        }

        return implode('; ', $css);
    }

    public function validateProps(array $props): array
    {
        return [];
    }

    public function transformPropsForStorage(array $props): array
    {
        return $props;
    }

    public function transformPropsFromStorage(array $props): array
    {
        return $props;
    }
}
