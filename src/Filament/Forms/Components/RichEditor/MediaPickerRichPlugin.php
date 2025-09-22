<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\RichEditor;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Js;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\Concerns\InteractsWithMediaLibraryModal;
use SolutionForest\InspireCms\TipTap\Marks\MediaPickerLink;

class MediaPickerRichPlugin implements RichContentPlugin
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
            app(MediaPickerLink::class),
        ];
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [
            FilamentAsset::getScriptSrc('rich-content-plugins/media-picker-extension', 'solution-forest/inspirecms'),
            FilamentAsset::getScriptSrc('rich-content-plugins/media-picker-mark', 'solution-forest/inspirecms'),
        ];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('testMediaPicker')
                ->icon('heroicon-o-cursor-arrow-ripple'),
            RichEditorTool::make('mediaPicker')
                ->label('Media Picker')
                ->iconAlias('inspirecms::media_picker')
                ->jsHandler(function (RichEditorTool $tool) {

                    $richEditorComponent = $tool->getEditor();

                    if (! $richEditorComponent) {
                        throw new \RuntimeException('RichEditor component instance not found.');
                    }

                    $key = $richEditorComponent->getKey();
                    $statePath = $richEditorComponent->getStatePath();

                    if (! in_array(InteractsWithMediaLibraryModal::class, class_uses($richEditorComponent))) {
                        throw new \RuntimeException('RichEditor component must use the InteractsWithMediaLibraryModal trait to use the Media Picker plugin.');
                    }

                    $mediaPickerModalId = $richEditorComponent->getMediaLibraryModalId();
                    $mediaPickerConfig = Js::from($richEditorComponent->getMediaLibraryModalConfig());

                    return <<<JS
                        \$dispatch('media-picker-setup', { 
                            selected: [],
                            key: '$key',
                            id: '$mediaPickerModalId',
                            config: $mediaPickerConfig,
                        });
                        \$dispatch('open-modal', { 
                            id: '$mediaPickerModalId',
                            key: '$key',
                            selected: [],
                        });
                    JS;
                })
                ->action('testMediaPicker'),
        ];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            Action::make('testMediaPicker')
                ->schema([
                    \SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\MediaPicker::make('code'),
                ])
                ->action(function ($data) {
                    // dd($data);
                }),
        ];
    }
}
