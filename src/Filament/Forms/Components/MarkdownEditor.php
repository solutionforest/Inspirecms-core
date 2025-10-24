<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\MarkdownEditor as BaseMarkdownEditor;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\Filter\BuilderFilter;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\MediaSelect;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MarkdownEditor extends BaseMarkdownEditor
{
    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.markdown-editor';

    /**
     * @return array<string | array<string>>
     */
    public function getDefaultToolbarButtons(): array
    {
        return [
            ['bold', 'italic', 'strike', 'link'],
            ['heading'],
            ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
            ['table', 'attachFiles'],
            ['contentPicker', 'mediaPicker'],
            ['undo', 'redo'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerActions([
            fn (self $component) => $component->getContentPickerAction(),
            fn (self $component) => $component->getMediaPickerAction(),
        ]);
    }

    public function getContentPickerAction()
    {
        return Action::make('contentPicker')
            ->label('Attach from content picker')
            ->slideOver()
            ->modalWidth('5xl')
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->schema([
                ContentTree::make('selection')
                    ->hiddenLabel()
                    ->columnSpan('full')
                    ->where(new BuilderFilter(scopeMethod: 'whereIsWebPage'))
                    ->maxItems(1),
            ])
            ->action(function (array $arguments, array $data, self $component) {

                $ids = $data['selection'] ?? [];

                if (empty($ids)) {
                    return;
                }

                $key = $this->getKey();
                $livewire = $this->getLivewire();

                $items = $this->formatContentPickerState($ids);

                $livewire->dispatch(
                    'append-custom-links-to-markdown-editor',
                    name: 'contentPicker',
                    awaitSchemaComponent: $key,
                    livewireId: $livewire->getId(),
                    key: $key,
                    data: $items,
                );
            });
    }

    public function getMediaPickerAction()
    {
        return Action::make('mediaPicker')
            ->label('Attach from media library')
            ->slideOver()
            ->modalWidth('screen')
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->extraModalWindowAttributes([
                'class' => 'media-library-browser-modal-content',
            ])
            ->modalSubmitActionLabel(__('inspirecms-support::media-library.buttons.select.label'))
            ->modalCancelActionLabel(__('inspirecms-support::media-library.buttons.cancel.label'))
            ->schema([
                MediaSelect::make('selection')
                    ->hiddenLabel()
                    ->columnSpan('full'),
            ])
            ->action(function (array $arguments, array $data, self $component) {

                $ids = $data['selection'] ?? [];

                if (empty($ids)) {
                    return;
                }

                $key = $this->getKey();
                $livewire = $this->getLivewire();

                $items = $this->formatMediaPickerState($ids);

                $livewire->dispatch(
                    'append-custom-links-to-markdown-editor',
                    name: 'mediaPicker',
                    awaitSchemaComponent: $key,
                    livewireId: $livewire->getId(),
                    key: $key,
                    data: $items,
                );
            });
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
            ->toArray();
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
            ->toArray();
    }

    protected function mutateMediaPickerState(Model | MediaAsset $mediaAsset)
    {
        /** @var null | Media */
        $media = $mediaAsset->getFirstMedia();
        $mediaUrl = $mediaAsset->getUrl(isAbsolute: false);
        $thumbnail = $mediaAsset->getThumbnailUrl(isAbsolute: false);
        $title = $media?->file_name ?? $media?->title ?? $mediaAsset->title;
        $mimeType = $media?->mime_type;

        $attributes = [
            'data-cmsmediaasset-id' => $mediaAsset->getKey(),
            'data-media-mime-type' => $mimeType,
        ];

        // Determine media type and tag
        $type = 'link'; // Default to link
        
        if ($mediaAsset->isImage()) {
            $type = 'img';

            $responsive = collect($mediaAsset->getResponsiveImages(isAbsolute: false))
                ->flatten(1)
                ->pluck('url', 'width')
                ->each(function ($url, $width) use (&$attributes) {
                    $attributes["data-image-responsive__{$width}"] = $url;
                });

            $attributes['data-media-thumbnail'] = $thumbnail;
        } 
        // Video handling
        elseif ($mimeType && str_starts_with($mimeType, 'video/')) {
            $type = 'video';
            
            // Add video-specific attributes
            $attributes['data-media-type'] = 'video';
            $attributes['controls'] = 'controls';
            $attributes['preload'] = 'metadata';

            $attributes['data-media-thumbnail'] = $thumbnail;
        }
        // Audio handling
        elseif ($mimeType && str_starts_with($mimeType, 'audio/')) {
            $type = 'audio';
            
            // Add audio-specific attributes
            $attributes['data-media-type'] = 'audio';
            $attributes['controls'] = 'controls';
            $attributes['preload'] = 'metadata';
        }

        // Convert attributes array to string
        $attributesString = collect($attributes)
            ->filter(fn($value) => !is_null($value) && $value !== '')
            ->map(fn($value, $key) => in_array($value, ['controls', 'metadata']) ? $key : "{$key}=\"{$value}\"")
            ->join(' ');

        $content = match ($type) {
            'video' => "<video src=\"{$mediaUrl}\" {$attributesString}></video>",
            'audio' => "<audio src=\"{$mediaUrl}\" {$attributesString}></audio>",
            default => null,
        };

        return [
            'url' => $mediaUrl,
            'title' => $title,
            'type' => $type,
            'content' => $content,
            'attributes' => $attributesString,
        ];
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

        $attributes = [
            'data-content-id' => $content->getKey(),
            'data-content-slug' => $content->slug,
        ];

        // Convert attributes array to string
        $attributesString = collect($attributes)
            ->map(fn ($value, $key) => "{$key}=\"{$value}\"")
            ->join(' ');

        return [
            'url' => $relativeUrl,
            'title' => $content->title,
            'type' => 'link',
            'attributes' => $attributesString,
        ];
    }
}
