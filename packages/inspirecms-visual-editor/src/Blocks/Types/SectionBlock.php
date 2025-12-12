<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Blocks\Types;

use SolutionForest\InspireCmsVisualEditor\Enums\BlockCategory;

class SectionBlock extends AbstractContainerBlock
{
    protected string $type = 'section';

    protected BlockCategory $category = BlockCategory::LAYOUT;

    protected string $label = 'Section';

    protected string $icon = 'heroicon-o-rectangle-group';

    protected string $description = 'A page section with full-width background and content container';

    public function getDefaultProps(): array
    {
        return [
            'display' => 'flex',
            'flexDirection' => 'column',
            'justifyContent' => 'center',
            'alignItems' => 'center',
            'gap' => '24px',
            'fullWidth' => true,
            'contentMaxWidth' => '1200px',
            'paddingY' => '64px',
            'paddingX' => '24px',
        ];
    }

    public function getSettingsSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Section Settings')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('props.sectionId')
                        ->label('Section ID')
                        ->placeholder('hero-section')
                        ->helperText('HTML ID for anchor links'),
                    \Filament\Forms\Components\Toggle::make('props.fullWidth')
                        ->label('Full Width Background')
                        ->default(true),
                    \Filament\Forms\Components\TextInput::make('props.contentMaxWidth')
                        ->label('Content Max Width')
                        ->placeholder('1200px'),
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('props.paddingY')
                                ->label('Vertical Padding')
                                ->placeholder('64px'),
                            \Filament\Forms\Components\TextInput::make('props.paddingX')
                                ->label('Horizontal Padding')
                                ->placeholder('24px'),
                        ]),
                    \Filament\Forms\Components\TextInput::make('props.minHeight')
                        ->label('Min Height')
                        ->placeholder('auto')
                        ->helperText('e.g., 100vh for full screen'),
                ]),
        ];
    }

    protected function renderDefault(array $props, array $children, array $styles, bool $isEditor): string
    {
        $childrenHtml = implode('', $children);

        $sectionId = isset($props['sectionId']) ? ' id="' . e($props['sectionId']) . '"' : '';

        $outerStyle = $this->buildSectionOuterStyle($props, $styles);
        $innerStyle = $this->buildSectionInnerStyle($props);

        $outerClasses = $isEditor ? 've-section' : '';
        $innerClasses = $isEditor ? 've-section-content' : '';

        return sprintf(
            '<section class="%s" data-block-type="%s"%s style="%s">
                <div class="%s" style="%s">%s</div>
            </section>',
            e($outerClasses),
            e($this->type),
            $sectionId,
            e($outerStyle),
            e($innerClasses),
            e($innerStyle),
            $childrenHtml
        );
    }

    protected function buildSectionOuterStyle(array $props, array $styles): string
    {
        $css = [];

        $css[] = 'width: 100%';
        $css[] = 'display: flex';
        $css[] = 'justify-content: center';

        if (isset($props['paddingY'])) {
            $css[] = "padding-top: {$props['paddingY']}";
            $css[] = "padding-bottom: {$props['paddingY']}";
        }

        if (isset($props['paddingX'])) {
            $css[] = "padding-left: {$props['paddingX']}";
            $css[] = "padding-right: {$props['paddingX']}";
        }

        if (isset($props['minHeight']) && $props['minHeight'] !== 'auto') {
            $css[] = "min-height: {$props['minHeight']}";
        }

        // Add background styles
        if (isset($styles['backgroundColor'])) {
            $css[] = "background-color: {$styles['backgroundColor']}";
        }

        if (isset($styles['backgroundImage'])) {
            $css[] = "background-image: {$styles['backgroundImage']}";
            $css[] = 'background-size: ' . ($styles['backgroundSize'] ?? 'cover');
            $css[] = 'background-position: ' . ($styles['backgroundPosition'] ?? 'center');
        }

        return implode('; ', $css);
    }

    protected function buildSectionInnerStyle(array $props): string
    {
        $css = [];

        $css[] = 'width: 100%';
        $css[] = 'max-width: ' . ($props['contentMaxWidth'] ?? '1200px');
        $css[] = 'display: ' . ($props['display'] ?? 'flex');
        $css[] = 'flex-direction: ' . ($props['flexDirection'] ?? 'column');
        $css[] = 'justify-content: ' . ($props['justifyContent'] ?? 'center');
        $css[] = 'align-items: ' . ($props['alignItems'] ?? 'center');

        if (isset($props['gap'])) {
            $css[] = "gap: {$props['gap']}";
        }

        return implode('; ', $css);
    }
}
