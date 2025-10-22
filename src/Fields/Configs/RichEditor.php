<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Composer\InstalledVersions;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\RichEditor\TextColor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
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
    use EditorBasicTrait {
        getAllAvailableToolbarButtons as protected getBaseAllAvailableToolbarButtons;
    }

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

    public array $predefinedTextColors = [];

    public bool $useDefaultTextColors = true;

    public bool $useCustomTextColors = true;

    public function getFormSchema(): array
    {
        return [
            Tabs::make('Advanced')
                ->columnSpanFull()
                ->extraAttributes([
                    'style' => 'min-height: 700px;',
                ])
                ->tabs([

                    Tab::make('Toolbar')
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
                                        ->suffixAction(
                                            Action::make('appendButton')
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
                        ]),

                    Tab::make('Plugins')
                        ->schema([
                            Flex::make([
                                Text::make(str(<<<'EOT'
                                    **Note**: 
                                    Some plugins may require additional setup, such as adding JavaScript or CSS assets to your application. 
                                    EOT)->inlineMarkdown()->toHtmlString())
                                    ->color('primary')
                                    ->grow(),

                                Action::make('doc')
                                    ->label('Documentation')
                                    ->url('https://filamentphp.com/docs/4.x/forms/rich-editor#extending-the-rich-editor')
                                    ->icon(Heroicon::OutlinedBookOpen)
                                    ->color('primary')
                                    ->openUrlInNewTab()
                                    ->link()
                            ]),

                            
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
                        ])
                        ->columnSpanFull(),
                    
                    Tab::make('Text Color Plugin Settings')
                        ->visible(fn () => self::isFilamentVersion41())
                        ->schema(function () {
                                
                            $components[] = Flex::make([

                                Text::make(str(<<<'EOT'
                                    **Note**: 
                                    Need to enable the Text Color on toolbar button to use this plugin. 
                                    EOT)->inlineMarkdown()->toHtmlString())
                                    ->color('primary')
                                    ->grow(),

                                Action::make('doc')
                                    ->label('Documentation')
                                    ->url('https://filamentphp.com/docs/4.x/forms/rich-editor#customizing-the-toolbar-buttons')
                                    ->icon(Heroicon::OutlinedBookOpen)
                                    ->color('primary')
                                    ->openUrlInNewTab()
                                    ->link()
                            ]);

                            $components[] = Toggle::make('useDefaultTextColors')
                                ->label('Use Default Text Colors')
                                ->helperText('Allows you to use the default text colors provided by Filament.')
                                ->default(true);

                            $components[] = Toggle::make('useCustomTextColors')
                                ->label('Use Custom Text Colors')
                                ->helperText('Enable to use the custom text colors by manually defining.')
                                ->default(false);

                            $components[] = Repeater::make('predefinedTextColors')
                                ->label('Predefined Text Colors')
                                ->columnSpanFull()
                                ->schema([
                                    TextInput::make('label')
                                        ->required()
                                        ->placeholder('E.g. Primary, Secondary, Danger ...')
                                        ->live(true, 5000)->afterStateUpdated(fn (callable $set, $state) => $set('name', str($state ?? '')->trim()->slug()->toString()))
                                        ->distinct(),
                                    TextInput::make('name')
                                        ->required()
                                        ->placeholder('E.g. primary, secondary, danger ...')
                                        ->distinct(),
                                    ColorPicker::make('color')
                                        ->required()
                                        ->label('Color (Light Mode)'),
                                    ColorPicker::make('darkColor')
                                        ->label('Color (Dark Mode)'),
                                ])
                                ->table([
                                    TableColumn::make('Label')
                                        ->markAsRequired(),
                                    TableColumn::make('Name')
                                        ->markAsRequired(),
                                    TableColumn::make('Color')
                                        ->markAsRequired()
                                        ->wrapHeader(),
                                    TableColumn::make('Dark Color')
                                        ->wrapHeader(),
                                ])
                                ->when(fn () => static::isFilamentVersion41(), fn (Repeater $component) => $component->compact())
                                ->addActionLabel('Add Color')
                                ->minItems(0);

                            return $components;
                        }),

                    Tab::make('File Attachments')
                        ->schema([
                            static::getEditorBasicTraitComponent('fileAttachmentsDisk'),
                            static::getEditorBasicTraitComponent('fileAttachmentsDirectory'),
                            static::getEditorBasicTraitComponent('fileAttachmentsVisibility'),
                        ]),
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

            if (self::isFilamentVersion41()) {
                $component->textColors($this->getTextColorsForRichEditor());
                $component->customTextColors($this->useCustomTextColors);
            }
        }
    }

    public function getTextColorsForRichEditor(): array
    {
        if (! self::isFilamentVersion41()) {
            return [];
        }

        return collect($this->predefinedTextColors)
            ->filter(fn ($color) => ! empty($color['name']) && ! empty($color['color']))
            ->keyBy('name')
            ->map(function ($item, $name) {
                return TextColor::make($item['label'] ?? $name, $item['color'], $item['darkColor'] ?? null);
            })
            ->when($this->useDefaultTextColors, fn (Collection $collection) => $collection->merge(TextColor::getDefaults()))
            ->all();
    }

    public static function getAllAvailableToolbarButtons(): array
    {
        $buttons = static::getBaseAllAvailableToolbarButtons();

        if (self::isFilamentVersion41()) {
            $extra = [
                'grid', 'gridDelete',
                'textColor', 'code',
            ];
            $buttons = array_merge($buttons, array_combine($extra, $extra));
        }

        return $buttons;
    }

    private static function isFilamentVersion41(): bool
    {
        if (
            ($filamentVersion = InstalledVersions::getVersion('filament/filament'))
            && version_compare($filamentVersion, '4.1.0', '>=')
        ) {
            return true;
        }

        return false;
    }
}
