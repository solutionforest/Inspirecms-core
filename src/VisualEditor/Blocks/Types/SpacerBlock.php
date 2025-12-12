<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\Blocks\Types;

use SolutionForest\InspireCms\VisualEditor\Enums\BlockCategory;

class SpacerBlock extends AbstractBlock
{
    protected string $type = 'spacer';

    protected BlockCategory $category = BlockCategory::LAYOUT;

    protected string $label = 'Spacer';

    protected string $icon = 'heroicon-o-arrows-up-down';

    protected string $description = 'Add vertical spacing between elements';

    public function getDefaultProps(): array
    {
        return [
            'height' => '48px',
            'heightMobile' => null,
            'heightTablet' => null,
        ];
    }

    public function getSettingsSchema(): array
    {
        return [
            \Filament\Forms\Components\Section::make('Spacer Settings')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('props.height')
                        ->label('Height')
                        ->required()
                        ->placeholder('48px')
                        ->helperText('e.g., 48px, 3rem, 10vh'),
                ]),
            \Filament\Forms\Components\Section::make('Responsive')
                ->collapsed()
                ->schema([
                    \Filament\Forms\Components\TextInput::make('props.heightMobile')
                        ->label('Mobile Height')
                        ->placeholder('24px'),
                    \Filament\Forms\Components\TextInput::make('props.heightTablet')
                        ->label('Tablet Height')
                        ->placeholder('36px'),
                ]),
        ];
    }

    public function getStyleSchema(): array
    {
        return [];
    }

    protected function renderDefault(array $props, array $children, array $styles, bool $isEditor): string
    {
        $height = $props['height'] ?? '48px';

        $editorClass = $isEditor ? 've-spacer' : '';
        $editorIndicator = $isEditor ? '<span style="display: block; border: 1px dashed #e5e7eb; height: 100%;"></span>' : '';

        return sprintf(
            '<div class="%s" data-block-type="%s" style="height: %s; width: 100%%;" aria-hidden="true">%s</div>',
            e($editorClass),
            e($this->type),
            e($height),
            $editorIndicator
        );
    }

    public function validateProps(array $props): array
    {
        $errors = [];

        if (empty($props['height'])) {
            $errors['height'] = 'Spacer height is required';
        }

        return $errors;
    }
}
