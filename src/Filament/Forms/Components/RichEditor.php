<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\RichEditor as BaseRichEditor;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\Concerns\InteractsWithMediaLibraryModal;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RichEditor extends BaseRichEditor
{
    use InteractsWithMediaLibraryModal;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.rich-editor';

    /**
     * @var array<string>
     */
    protected array | Closure $toolbarButtons = [
        'attachFiles',
        'blockquote',
        'bold',
        'bulletList',
        'codeBlock',
        'h2',
        'h3',
        'italic',
        'link',
        'orderedList',
        'redo',
        'strike',
        'underline',
        'undo',
        'contentPicker',
        'mediaPicker',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->extraAlpineAttributes([
            'x-init' => $this->getExtraTrixActionsRegEvent(),
        ]);

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

    protected function getExtraTrixActionsRegEvent()
    {
        return <<<'JS'
            document.addEventListener("trix-action-invoke", function(event) {
                const { target, invokingElement, actionName } = event
                switch (actionName) {
                    case 'x-content-picker':
                        $dispatch('content-picker-trix-click');
                        break;
                    case 'x-media-picker':
                        $dispatch('media-picker-trix-click');
                        break;
                    default:
                        break;
                }
            });
        JS;
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
            ->all();
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
            ->map(fn (Model | Content $record) => $this->mutateContentPickerState($record))->all();
    }

    protected function mutateMediaPickerState(Model | MediaAsset $mediaAsset)
    {
        /** @var null | Media */
        $media = $mediaAsset->getFirstMedia();
        $mediaUrl = $mediaAsset->getUrl(isAbsolute: false);
        $title = $media?->title ?? $mediaAsset->title;

        $data = [
            'contentType' => $media?->mime_type,
            'filename' => $media?->file_name,
            'href' => $mediaUrl,
            'url' => $mediaUrl,
            'id' => $mediaAsset->getKey(),
            'title' => $title,
            'caption' => $mediaAsset->caption,
            'description' => $mediaAsset->description,
            'name' => $mediaAsset->caption,
        ];

        if ($mediaAsset->isImage()) {
            // Define conversion sizes with their widths
            $conversions = [
                'thumbnail' => 150,
                'small' => 300,
                'medium' => 600,
                'large' => 1200,
            ];

            // Build sizes attribute for responsive images
            $sizesAttr = collect([
                '(max-width: 300px) 300px',
                '(max-width: 600px) 600px',
                '(max-width: 1200px) 1200px',
                '100vw',
            ])->implode(', ');

            // Generate URLs for each conversion
            $conversionUrls = collect($conversions)
                ->mapWithKeys(fn ($width, $conversion) => [
                    $conversion => $mediaAsset->getUrl($conversion, false),
                ])
                ->all();

            // Build srcset string
            $srcset = collect($conversions)
                ->map(function ($width, $conversion) use ($mediaAsset) {
                    $url = $mediaAsset->getUrl($conversion, false);

                    return "{$url} {$width}w";
                })
                ->implode(', ');

            // Create responsive image HTML with srcset
            $content = sprintf(
                '<img src="%s" alt="%s" srcset="%s" sizes="%s" loading="lazy" class="trix-attachment-image trix-attachment-mediapicker">',
                htmlspecialchars($mediaUrl),
                htmlspecialchars($title),
                htmlspecialchars($srcset),
                $sizesAttr
            );

            $data['sizes'] = $conversionUrls;
            $data['content'] = $content;
            $data['srcset'] = $srcset;
        }

        return $data;
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

        $template = ' <a href="%s" class="trix-attachment-contentpicker" data-id="%s" data-slug="%s">%s</a> ';

        return sprintf(
            $template,
            htmlspecialchars($relativeUrl),
            htmlspecialchars($content->getKey()),
            htmlspecialchars($content->slug),
            htmlspecialchars($content->title)
        );
    }
}
