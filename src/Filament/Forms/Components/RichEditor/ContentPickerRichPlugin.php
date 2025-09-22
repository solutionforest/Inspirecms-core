<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\RichEditor;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Facades\FilamentAsset;
use SolutionForest\InspireCms\TipTap\Marks\ContentPickerLink;

class ContentPickerRichPlugin implements RichContentPlugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @return array<Extension>
     */
    public function getTipTapPhpExtensions(): array
    {
        return [
            app(ContentPickerLink::class),
        ];
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [
            FilamentAsset::getScriptSrc('rich-content-plugins/content-picker-extension', 'solution-forest/inspirecms'),
            FilamentAsset::getScriptSrc('rich-content-plugins/content-picker-mark', 'solution-forest/inspirecms'),
        ];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('contentPicker')
                ->label('Content Picker')
                ->iconAlias('inspirecms::content_picker')
                ->jsHandler(function (RichEditorTool $tool) {

                    $richEditorComponent = $tool->getEditor();

                    if (! $richEditorComponent) {
                        throw new \RuntimeException('RichEditor component instance not found.');
                    }

                    $key = $richEditorComponent->getKey();
                    $contentPickerModalId = 'content-tree-picker-modal';

                    return <<<JS
                        \$dispatch('x-content-picker-modal-setup', { 
                            selected: [],
                            key: '$key',
                            id: '$contentPickerModalId',
                            config: [],
                        });
                        \$dispatch('open-modal', { 
                            id: '$contentPickerModalId',
                            key: '$key',
                            selected: [],
                        });
                    JS;
                })
                ->action('attachFromContentPicker'),
        ];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            Action::make('attachFromContentPicker')
                ->schema([
                    \SolutionForest\InspireCms\Filament\Forms\Components\ContentPicker::make('content')
                        ->hiddenLabel()
                        ->columnSpan('full'),
                ])
                ->action(function ($data) {
                    // Action logic here
                    ray($data);
                }),
        ];
    }
}
