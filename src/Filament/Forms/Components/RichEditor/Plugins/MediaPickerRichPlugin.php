<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\RichEditor\Plugins;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Forms\Components\RichEditor\TipTapExtensions\CmsMediaAssetExtension;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\MediaSelect;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
            app(CmsMediaAssetExtension::class),
        ];
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [
            FilamentAsset::getScriptSrc('rich-content-plugins/extension-cms-media-link', 'solution-forest/inspirecms'),
        ];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('mediaPicker')
                ->label('Media Picker')
                ->iconAlias('inspirecms::media_picker')
                ->action('attachFromMediaLibrary', arguments: '{ id: $getEditor().getAttributes(\'cmsMediaAsset\')[\'id\'] ?? null }')
                ->activeKey('cmsMediaAsset'),
        ];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            Action::make('attachFromMediaLibrary')
                ->slideOver()
                ->modalWidth('screen')
                ->stickyModalHeader()
                ->stickyModalFooter()
                ->extraModalWindowAttributes([
                    'class' => 'media-library-browser-modal-content',
                ])
                ->modalSubmitActionLabel(__('inspirecms-support::media-library.buttons.select.label'))
                ->modalCancelActionLabel(__('inspirecms-support::media-library.buttons.cancel.label'))
                ->fillForm(fn (array $arguments) => [
                    'selection' => array_filter([$arguments['id'] ?? null]),
                ])
                ->schema([
                    MediaSelect::make('selection')
                        ->hiddenLabel()
                        ->columnSpan('full'),
                ])
                ->action(function (array $arguments, array $data, RichEditor $component) {

                    $ids = $data['selection'] ?? [];
                    if (empty($ids)) {
                        return;
                    }

                    $items = $this->formatMediaPickerState($ids);
                    foreach ($items as $item) {
                        $component->runCommands(
                            [
                                EditorCommand::make(
                                    'insertContent',
                                    arguments: [[
                                        'type' => 'cmsMediaAsset',
                                        'attrs' => $item,
                                    ]],
                                ),
                            ],
                            editorSelection: $arguments['editorSelection'],
                        );
                    }
                }),
        ];
    }

    protected function formatMediaPickerState($state)
    {
        if (! is_array($state)) {
            $state = is_null($state) || empty($state) ? [] : [$state];
        }
        if (empty($state)) {
            return [];
        }

        return InspireCmsConfig::getMediaAssetModelClass()::query()
            ->with(['media'])
            ->findMany($state)
            ->filter(fn ($record) => $record instanceof MediaAsset)
            ->sortBy(fn (Model $record) => array_search($record->getKey(), $state))
            ->map(fn (Model | MediaAsset $record) => $this->mutateMediaPickerState($record))
            ->all();
    }

    protected function mutateMediaPickerState(Model | MediaAsset $mediaAsset)
    {
        /** @var null | Media */
        $media = $mediaAsset->getFirstMedia();
        $mediaUrl = $mediaAsset->getUrl(isAbsolute: false);
        $thumbnailUrl = $mediaAsset->getThumbnailUrl(isAbsolute: false);
        $title = $media?->title ?? $mediaAsset->title;

        return [
            'id' => $mediaAsset->getKey(),
            'url' => $mediaUrl,
            'thumbnailUrl' => $thumbnailUrl,
            'title' => $title,
            'filename' => $media?->file_name,
            'mimeType' => $media?->mime_type,
        ];
    }
}
