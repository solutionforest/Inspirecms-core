<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\MarkdownEditor as BaseMarkdownEditor;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\Concerns\InteractsWithMediaLibraryModal;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MarkdownEditor extends BaseMarkdownEditor
{
    use InteractsWithMediaLibraryModal;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.markdown-editor';

    /**
     * @var array<string>
     */
    protected array | Closure $toolbarButtons = [
        'attachFiles',
        'blockquote',
        'bold',
        'bulletList',
        'codeBlock',
        'heading',
        'italic',
        'link',
        'orderedList',
        'redo',
        'strike',
        'table',
        'undo',
        // extra
        'contentPicker',
        'mediaPicker',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerActions([
            Action::make('selectContent')
                ->slideOver()
                ->fillForm(['selection' => []])
                ->form([
                    ContentTree::make('selection')->hiddenLabel(),
                ])
                ->action(function (array $data, self $component) {
                    $component->getLivewire()->dispatch(
                        'content-picker-trix-appead',
                        statePath: $component->getStatePath(),
                        data: $component->formatContentPickerState($data['selection']),
                    );
                }),
        ]);

        $this->registerListeners([
            'mediaPicker::select' => [
                function (self $component, string $statePath, $ids = null, $callback = null) {
                    if ($statePath === $component->getStatePath() && ! empty($ids)) {
                        $component->getLivewire()->dispatch(
                            'media-picker-trix-appead',
                            statePath: $statePath,
                            data: $component->formatMediaPickerState($ids),
                        );
                    }
                },
            ],
        ]);
    }

    public function formatMediaPickerState($state)
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
            ->implode(' ');
    }

    public function formatContentPickerState($state)
    {
        if (! is_array($state)) {
            $state = is_null($state) || empty($state) ? [] : [$state];
        }
        if (empty($state)) {
            return [];
        }

        return InspireCmsConfig::getContentModelClass()::query()
            ->findMany($state)
            ->filter(fn ($record) => $record instanceof Content)
            ->sortBy(fn (Model $record) => array_search($record->getKey(), $state))
            ->map(fn (Model | Content $record) => $this->mutateContentPickerState($record))
            ->implode(' ');
    }

    protected function mutateMediaPickerState(Model | MediaAsset $mediaAsset)
    {
        /** @var null | Media */
        $media = $mediaAsset->getFirstMedia();
        $mediaUrl = $mediaAsset->getUrl(isAbsolute: false);
        $title = $media?->title ?? $mediaAsset->title;

        if ($mediaAsset->isImage()) {
            $template = '![%s](%s)';
        } else {
            $template = '[%s](%s)';
        }
        $template .= '{data-cmsmediaasset-id="%s"}';

        return sprintf(
            $template,
            htmlspecialchars($title),
            htmlspecialchars($mediaUrl),
            htmlspecialchars($mediaAsset->getKey()),
        );
    }

    protected function mutateContentPickerState(Model | Content $content)
    {
        $livewire = $this->getLivewire();
        $translatedLocale = null;
        foreach ([
            'getActiveLocale',
            'getActiveFormsLocale',
            'getLocale',
        ] as $method) {
            try {
                if (method_exists($livewire, $method)) {
                    $translatedLocale = $livewire->{$method}();

                    break;
                }
            } catch (\Throwable $th) {
                $translatedLocale = null;
            }
        }

        if (! is_string($translatedLocale) || empty($translatedLocale)) {
            $translatedLocale = app()->getLocale();
        }

        $url = $content->getUrl($translatedLocale);

        $relativeUrl = str($url)
            ->replaceFirst(url(''), '')
            ->trim()->trim('/')
            ->prepend('/')
            ->toString();

        $template = '[%s](%s){data-cmscontent-id="%s" data-cmscontent-slug="%s"}';

        return sprintf(
            $template,
            htmlspecialchars($content->title),
            htmlspecialchars($relativeUrl),
            htmlspecialchars($content->getKey()),
            htmlspecialchars($content->slug),
        );
    }
}
