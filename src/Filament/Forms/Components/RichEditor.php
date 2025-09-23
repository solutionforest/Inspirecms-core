<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Filament\Forms\Components\RichEditor as BaseRichEditor;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Forms\Components\RichEditor\ContentPickerRichPlugin;
use SolutionForest\InspireCms\Filament\Forms\Components\RichEditor\MediaPickerRichPlugin;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\Concerns\InteractsWithMediaLibraryModal;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RichEditor extends BaseRichEditor
{
    use InteractsWithMediaLibraryModal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugins([
            ContentPickerRichPlugin::make(),
            MediaPickerRichPlugin::make(),
        ]);

        $this->extraInputAttributes(fn (self $component) => [
            'x-fi-fo-key' => $component->getKey(),
            'x-on:update-content-picker-selection.window' => <<<'JS'
                const key = $el.getAttribute('x-fi-fo-key');
                if ($event?.detail?.key !== key) {
                    return;
                }
                const editor = $getEditor() || null;
                if (!editor) {
                    console.warn('Editor instance not found');
                    return;
                }
                $wire
                    .callSchemaComponentMethod(
                        key,
                        'appendFromContentPicker',
                        { ids: $event.detail?.data || [] },
                    )
                    .then((urls) => {
                        if (urls && urls.length > 0) {
                            editor?.chain()?.focus()?.insertContentPickerLinks(urls)?.run();
                        }
                    });
            JS,
            'x-on:update-media-picker-selection.window' => <<<'JS'
                const key = $el.getAttribute('x-fi-fo-key');
                if ($event?.detail?.key !== key) {
                    return;
                }
                const editor = $getEditor() || null;
                if (!editor) {
                    console.warn('Editor instance not found');
                    return;
                }
                $wire
                    .callSchemaComponentMethod(
                        key,
                        'appendFromMediaPicker',
                        { ids: $event.detail?.data || [] },
                    )
                    .then((urls) => {
                        if (urls && urls.length > 0) {
                            editor?.chain()?.focus()?.insertMediaPickerLinks(urls)?.run();
                        }
                    });
            JS,
        ]);
    }

    #[ExposedLivewireMethod]
    public function appendFromContentPicker($ids)
    {
        if (! empty($ids)) {
            return $this->formatContentPickerState($ids);
        }

        return '';
    }

    #[ExposedLivewireMethod]
    public function appendFromMediaPicker($ids)
    {
        if (! empty($ids)) {
            return $this->formatMediaPickerState($ids);
        }

        return '';
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
            'mimeType' => $media?->mime_type,
            'mediaId' => $mediaAsset->getKey(),
            'filename' => $media?->file_name,
            'href' => $mediaUrl,
            'url' => $mediaUrl,
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

            // // Create responsive image HTML with srcset
            // $content = sprintf(
            //     '<img src="%s" alt="%s" srcset="%s" sizes="%s" loading="lazy" class="trix-attachment-image trix-attachment-mediapicker">',
            //     htmlspecialchars($mediaUrl),
            //     htmlspecialchars($title),
            //     htmlspecialchars($srcset),
            //     $sizesAttr
            // );

            $data['sizes'] = $conversionUrls;
            // $data['content'] = $content;
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

        return [
            'url' => $relativeUrl,
            'title' => $content->title,
            'key' => $content->getKey(),
            'slug' => $content->slug,
        ];
    }
}
