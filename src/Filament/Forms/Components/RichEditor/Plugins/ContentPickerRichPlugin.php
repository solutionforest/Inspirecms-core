<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\RichEditor\Plugins;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\Filter\BuilderFilter;
use SolutionForest\InspireCms\Filament\Forms\Components\RichEditor\TipTapExtensions\CmsContentLinkExtension;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;

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
            app(CmsContentLinkExtension::class),
        ];
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [
            FilamentAsset::getScriptSrc('rich-content-plugins/extension-cms-content-link', 'solution-forest/inspirecms'),
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
                ->action('attachFromContentPicker', arguments: '{ id: $getEditor().getAttributes(\'cmsContentLink\')[\'id\'] ?? null, shouldOpenInNewTab: $getEditor().getAttributes(\'cmsContentLink\')[\'target\'] === \'_blank\' }')
                ->activeKey('cmsContentLink'),
        ];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            Action::make('attachFromContentPicker')
                ->slideOver()
                ->modalWidth('5xl')
                ->stickyModalHeader()
                ->stickyModalFooter()
                ->fillForm(fn (array $arguments) => [
                    'selection' => array_filter([$arguments['id'] ?? null]),
                    'shouldOpenInNewTab' => $arguments['shouldOpenInNewTab'] ?? false,
                ])
                ->schema([
                    Checkbox::make('shouldOpenInNewTab'),
                    ContentTree::make('selection')
                        ->hiddenLabel()
                        ->columnSpan('full')
                        ->where(new BuilderFilter(scopeMethod: 'whereIsWebPage'))
                        ->maxItems(1),
                ])
                ->action(function (array $arguments, array $data, RichEditor $component, $livewire) {

                    $isSingleCharacterSelection = ($arguments['editorSelection']['head'] ?? null) === ($arguments['editorSelection']['anchor'] ?? null);

                    $id = collect($data['selection'] ?? [])->filter()->last();

                    if (empty($id)) {

                        $component->runCommands(
                            [
                                EditorCommand::make(
                                    'toggleCmsContentLink',
                                ),
                            ],
                            editorSelection: $arguments['editorSelection'],
                        );

                        return;
                    }

                    $item = Arr::first($this->formatContentPickerState($id, $livewire));

                    $attrs = [
                        ...$item,
                        'target' => $data['shouldOpenInNewTab'] ? '_blank' : null,
                    ];

                    if (! $isSingleCharacterSelection && ($arguments['editorSelection']['type'] ?? '') === 'text') {
                        // Convert selected text to cmsContentLink
                        $component->runCommands(
                            [
                                EditorCommand::make(
                                    'toggleCmsContentLink',
                                    arguments: [[
                                        'attributes' => $attrs,
                                    ]],
                                ),
                            ],
                            editorSelection: $arguments['editorSelection'],
                        );

                    } else {
                        // Insert new cmsContentLink with title as content
                        $textContent = $item['title'] ?? 'Content Link';

                        $component->runCommands(
                            [
                                EditorCommand::make(
                                    'insertCmsContentLink',
                                    arguments: [[
                                        'attributes' => $attrs,
                                        'content' => $textContent,
                                    ]],
                                ),
                            ],
                            editorSelection: $arguments['editorSelection'],
                        );
                    }
                }),
        ];
    }

    protected function formatContentPickerState($state, $livewire)
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
            ->map(fn (Model | Content $record) => $this->mutateContentPickerState($record, $livewire))
            ->all();
    }

    protected function mutateContentPickerState(Model | Content $content, $livewire)
    {
        $translatedLocale = null;
        foreach ([
            'getActiveLocale',
            'getActiveFormsLocale',
            'getLocale',
        ] as $method) {
            try {
                if ($livewire && method_exists($livewire, $method)) {
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
            'id' => $content->getKey(),
            'url' => $relativeUrl,
            'title' => $content->title,
            'slug' => $content->slug,
        ];
    }
}
