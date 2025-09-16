<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Converter;
use SolutionForest\InspireCms\Fields\Configs\Concerns\EditorBasicTrait;
use SolutionForest\InspireCms\Fields\Converters\RichEditorConverter;
use SolutionForest\InspireCms\Filament\Forms\Components\RichEditor as FormsRichEditor;

#[ConfigName('richEditor', 'Rich Editor', 'Rich', 'heroicon-o-document-text')]
#[FormComponent(FormsRichEditor::class)]
#[DbType('mysql', 'text')]
#[DbType('sqlite', 'text')]
#[Converter(RichEditorConverter::class)]
class RichEditor extends FieldTypeBaseConfig implements FieldTypeConfig
{
    use EditorBasicTrait;

    protected static array $availableToolbarButtons = [
        
        'bold', 'italic', 'underline', 'strike', 'subscript', 'superscript',
        
        'attachFiles', 'link',

        'blockquote', 'codeBlock',

        'bulletList', 'orderedList',

        'h1', 'h2', 'h3',
    
        'tableAddColumnBefore', 'tableAddColumnAfter', 'tableDeleteColumn',
        'tableAddRowBefore', 'tableAddRowAfter', 'tableDeleteRow',
        'tableMergeCells', 'tableSplitCell',
        'tableToggleHeaderRow',
        'tableDelete',

        'contentPicker', 'mediaPicker',
        
        'redo', 'undo',
    ];

    public array $plugins = [];

    private static string $toolbarBtnDocUrl = 'https://filamentphp.com/docs/4.x/forms/rich-editor#customizing-the-toolbar-buttons';

    public array $floatingToolbars = [];

    public function getFormSchema(): array
    {
        return [
            Section::make()
                ->schema([

                    static::getEditorBasicTraitComponent('toolbarButtonType'),

                    static::getEditorBasicTraitComponent('toolbarButtons')
                        ->hintAction(
                            Action::make('doc')
                                ->label('Available buttons')
                                ->url(self::$toolbarBtnDocUrl)
                                ->icon(Heroicon::OutlinedBookOpen)
                                ->color('primary')
                                ->openUrlInNewTab(),
                        )
                        ->visible(fn ($get) => $get('toolbarButtonType') === 'buttons'),

                    static::getEditorBasicTraitComponent('toolbarButtonGroups')
                        ->hintAction(
                            Action::make('doc')
                                ->label('Available buttons')
                                ->url(self::$toolbarBtnDocUrl)
                                ->icon(Heroicon::OutlinedBookOpen)
                                ->color('primary')
                                ->openUrlInNewTab(),
                        )
                        ->visible(fn ($get) => $get('toolbarButtonType') === 'buttonGroups'),

                    Repeater::make('floatingToolbars')
                        ->columns(3)
                        ->hintAction(
                            Action::make('doc')
                                ->label('Available buttons')
                                ->url('https://filamentphp.com/docs/4.x/forms/rich-editor#customizing-floating-toolbars')
                                ->icon(Heroicon::OutlinedBookOpen)
                                ->color('primary')
                                ->openUrlInNewTab(),
                        )
                        ->table([
                            TableColumn::make('Node'),
                            TableColumn::make('Buttons'),
                        ])
                        ->schema([
                            TextInput::make('node')
                                ->columnSpan(1)
                                ->required()
                                ->placeholder('e.g. paragraph, heading, table ...')
                                ->datalist([
                                    'paragraph', 'heading', 'table', 'image', 'link', 'list', 'blockquote', 'codeBlock',
                                ]),
                            TagsInput::make('buttons')
                                ->columnSpan(2)
                                ->suggestions(static::getAllAvailableToolbarButtons())
                                ->reorderable()
                                ->placeholder('Add Toolbar Button (Enter to add)')
                                ->suffixAction(Action::make('appendButton')
                                    ->icon(Heroicon::Plus)
                                    ->fillForm(['buttons' => []])
                                    ->schema([
                                        CheckboxList::make('buttons')
                                            ->options(static::getAllAvailableToolbarButtonsOptions())
                                            ->columns(3)
                                            ->bulkToggleable()
                                            ->hiddenLabel(),
                                    ])
                                    ->action(function ($state, $data, TagsInput $component) {
                                        $original = $state ?? [];
                                        // append new buttons to original state
                                        $new = array_values(array_diff($data['buttons'] ?? [], $original));
                                        $state = array_merge($original, $new);
                                        $component->state($state);
                                    })
                                    ->size('sm')
                                ),
                        ])
                        ->addActionLabel('Add Group')
                        ->cloneable(),

                    Repeater::make('plugins')
                        ->label('Plugins')
                        ->simple(
                            TextInput::make('fqcn')
                                ->placeholder('Fully Qualified Class Name of the plugin, e.g. App\\Filament\\Forms\\RichEditors\\Plugins\\AttachFilesPlugin')
                                ->required()
                        )
                        ->columnSpanFull()
                        ->minItems(0)
                        ->defaultItems(0)
                        ->hint('Add plugins to enable corresponding toolbar buttons')
                        ->hintAction(
                            Action::make('doc')
                                ->label('Documentation')
                                ->url('https://filamentphp.com/docs/4.x/forms/rich-editor#registering-rich-content-attributes')
                                ->icon(Heroicon::OutlinedBookOpen)
                                ->color('primary')
                                ->openUrlInNewTab()
                        )
                        ->extraItemActions([
                            Action::make('clear')
                                ->icon(Heroicon::XMark)
                                ->color('gray')
                                ->tooltip('Clear')
                                ->action(function (array $arguments, Repeater $component): void {
                                    if (isset($arguments['item'])) {
                                        $state = $component->getState() ?? [];
                                        $state[$arguments['item']]['fqcn'] = null;
                                        $component->state($state);
                                    }
                                }),
                        ])
                        ->addActionLabel('Add Plugin')
                        ->deletable()
                        ->cloneable(),
                ]),
            Section::make('File Attachments')
                ->schema([
                    static::getEditorBasicTraitComponent('fileAttachmentsDisk'),
                    static::getEditorBasicTraitComponent('fileAttachmentsDirectory'),
                    static::getEditorBasicTraitComponent('fileAttachmentsVisibility'),
                ]),
        ];
    }

    public function applyConfig(Component $component): void
    {
        if ($component instanceof FormsRichEditor) {
            switch ($this->toolbarButtonType) {
                case 'buttons':
                    $component->toolbarButtons($this->toolbarButtons);
                    break;
                case 'buttonGroups':
                    $component->toolbarButtons(
                        collect($this->toolbarButtonGroups)
                            ->pluck('buttons')
                            ->all()
                    );
                    break;
                default:
                    // default to buttons
                    $component->toolbarButtons($this->toolbarButtons);
            }
            if (filled($this->floatingToolbars)) {
                $component->floatingToolbars(collect($this->floatingToolbars)->pluck('buttons', 'element')->all());
            }

            if (filled($this->fileAttachmentsDisk)) {
                $component->fileAttachmentsDisk($this->fileAttachmentsDisk);
            }
            if (filled($this->fileAttachmentsDirectory)) {
                $component->fileAttachmentsDirectory($this->fileAttachmentsDirectory);
            }
            if (filled($this->fileAttachmentsVisibility)) {
                $component->fileAttachmentsVisibility($this->fileAttachmentsVisibility);
            }
            if (filled($this->plugins)) {
                $component->plugins(
                    collect($this->plugins)
                        ->filter()
                        ->reject(fn ($v) => ! is_string($v) || empty($v))
                        ->reject(fn ($fqcn) => ! class_exists($fqcn))
                        ->unique()
                        ->values()
                        ->all()
                );
            }
        }
    }
}
