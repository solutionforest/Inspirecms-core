<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Filament\Forms\Components\BelongsToParentSelect;
use SolutionForest\InspireCms\Filament\Forms\Components\FieldGroupRepeater;
use SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource\Pages;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class DocumentTypeResource extends Resource
{
    protected static ?int $navigationSort = -10;

    protected static ?string $navigationIcon = null;

    public static function form(Form $form): Form
    {
        $morphFieldGroupsSortColumn = 'order';

        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Section::make()
                    ->columns(1)
                    ->schema([

                        Forms\Components\TextInput::make('title')
                            ->label(__('inspirecms::inspirecms.title'))
                            ->inlineLabel()
                            ->columnSpanFull()
                            ->required(),

                        // Display parent field group used
                        Forms\Components\Repeater::make('parentFieldGroups')
                            ->label(__('inspirecms::inspirecms.parent_xxx', ['name' => strtolower(Str::plural(__('inspirecms::inspirecms.field_group')))]))
                            ->addable(false)->reorderable(false)->deletable(false)
                            ->schema(static::getFieldGroupsRepeaterSchema())
                            ->extraAttributes(['class' => 'preview-fields-with-bg']),

                        FieldGroupRepeater::make('morphFieldGroups')
                            ->columnSpanFull()
                            ->addActionLabel(fn () => __('inspirecms::inspirecms.add_xxx', ['name' => Str::lower(__('inspirecms::inspirecms.field_group'))]))
                            ->label(Str::plural(__('inspirecms::inspirecms.field_group')))
                            ->validationAttribute(Str::lower(Str::plural(__('inspirecms::inspirecms.field_group'))))
                            ->collapsible()
                            ->reorderable()->orderColumn($morphFieldGroupsSortColumn)
                            ->reorderableWithButtons()
                            ->addAction(fn (Forms\Components\Actions\Action $action) => $action->extraAttributes(['class' => 'w-full'], true))
                            ->fieldGroupRecordOrderAttribute(static::getFieldGroupSortColumn())
                            // ->modifyRecordSelectUsing(fn ($select) => $select)   // custom select field to add field group
                            ->modifyRecordSelectOptionQueryUsing(function ($query, FieldGroupRepeater $component) {

                                $existingFieldGroupIds = array_values(
                                    array_filter(
                                        array_map(
                                            fn ($item) => is_array($item) ? data_get($item, 'field_group_id') : null,
                                            $component->getState() ?? [],
                                        )
                                    )
                                );

                                if (count($existingFieldGroupIds) > 0) {
                                    $query->whereKeyNot($existingFieldGroupIds);
                                }

                                return $query
                                    ->with(['fields']) // load preview
                                    ->where('active', true);
                            })
                            ->itemStateFromAttachFieldGroupUsing(function (array $data, $form, FieldGroupRepeater $component) {
                                $id = $data['recordId'] ?? null;
                                if ($id === null) {
                                    return [];
                                }

                                $fieldGroup = $component->getFieldGroupRelationshipQuery()->find($id);

                                return static::getFieldGroupsItemStateFromFieldGroup($fieldGroup);
                            })
                            ->itemLabel(fn (array $state): ?string => data_get($state, 'field_group_title'))
                            ->loadStateFromRelationshipsUsing(function ($record, FieldGroupRepeater $component) use ($morphFieldGroupsSortColumn) {
                                $records = $component->getRelationship()->with(['fieldGroup'])->orderBy($morphFieldGroupsSortColumn)->get();
                                $state = collect($records)
                                    ->pluck('fieldGroup')
                                    ->mapWithKeys(fn ($fieldGroup) => [
                                        (string) Str::uuid() => static::getFieldGroupsItemStateFromFieldGroup($fieldGroup),
                                    ])
                                    ->toArray();
                                $component->state($state);
                            })
                            ->schema(static::getFieldGroupsRepeaterSchema()),
                    ]),
            ]);
    }

    public static function detailInfoForm(Form $form): Form
    {
        $getFieldGroupsItemStatesFromParent = function ($parentId) {
            if (! $parentId) {
                return [];
            }
            $parent = static::getEloquentQuery()->with('fieldGroups')->find($parentId);
            if (! $parent) {
                return [];
            }
            $parentAncestors = collect($parent->ancestors())->push($parent);
            $state = $parentAncestors->pluck('fieldGroups')
                ->flatMap(fn ($fieldGroups) => collect($fieldGroups)->map(fn ($fieldGroup) => static::getFieldGroupsItemStateFromFieldGroup($fieldGroup)))
                ->mapWithKeys(fn ($stateItem) => [(string) Str::uuid() => $stateItem])
                ->toArray();

            return $state;
        };

        return $form
            ->columns(1)
            ->schema([

                BelongsToParentSelect::make('parent_id')
                    ->label(__('inspirecms::inspirecms.parent'))
                    ->nestableParentRelationship('parent', 'title', ignoreRecord: true)
                    ->searchable(['title'])
                    ->preload()
                    ->placeholder('(' . strtolower(__('inspirecms::inspirecms.no_parent') . ')'))
                    ->hintIcon(
                        'heroicon-o-information-circle',
                        __('inspirecms::inspirecms.document_types.empty_parent_description', [
                            'name' => strtolower(__('inspirecms::inspirecms.page')),
                            'documentType' => strtolower(__('inspirecms::inspirecms.document_type')),
                        ])
                    )
                    ->live()
                    ->afterStateUpdated(function ($state, $livewire) use ($getFieldGroupsItemStatesFromParent) {
                        $livewire->data['parentIdIndicator'] = $state;
                        $livewire->data['parentFieldGroups'] = $getFieldGroupsItemStatesFromParent($state);
                    })
                    ->afterStateHydrated(function ($state, $livewire) use ($getFieldGroupsItemStatesFromParent) {
                        $livewire->data['parentIdIndicator'] = $state;
                        $livewire->data['parentFieldGroups'] = $getFieldGroupsItemStatesFromParent($state);
                    })
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('created_at')->disabled()->inlineLabel(false)
                    ->afterStateHydrated(fn ($state, $component) => $component->state($state ? Carbon::parse($state)->shortRelativeToNowDiffForHumans() : null)),
                Forms\Components\TextInput::make('updated_at')->disabled()->inlineLabel(false)
                    ->afterStateHydrated(fn ($state, $component) => $component->state($state ? Carbon::parse($state)->shortRelativeToNowDiffForHumans() : null)),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_root_level')
                    ->label(__('inspirecms::inspirecms.is_root_level'))
                    ->getStateUsing(fn ($record) => $record?->isRoot() ?? false)
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentTypes::route('/'),
            'create' => Pages\CreateDocumentType::route('/create'),
            'edit' => Pages\EditDocumentType::route('/{record}/edit'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getDocumentTypeModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.document_type');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('inspirecms::inspirecms.setting');
    }

    protected static function getFieldGroupsItemStateFromFieldGroup(?Model $fieldGroup)
    {
        if ($fieldGroup === null) {
            return [];
        }
        $fieldGroupSortColumn = static::getFieldGroupSortColumn();

        return [
            'field_group_id' => $fieldGroup->getKey(),
            'field_group_title' => $fieldGroup->title,
            'field_group_fields' => $fieldGroup->fields
                ?->sortBy($fieldGroupSortColumn)
                ->map(fn ($field) => $field->only([
                    'label',
                    'type',
                ]))
                ->toArray(),
        ];
    }

    protected static function getFieldGroupsRepeaterSchema(): array
    {
        return [
            Forms\Components\Hidden::make('field_group_id'),
            Forms\Components\Hidden::make('field_group_title'),
            Forms\Components\Hidden::make('field_group_fields'),
            Forms\Components\Placeholder::make('preview_fields')
                ->label(__('inspirecms::inspirecms.preview_fields'))
                ->content(function ($get) {

                    $fieldsData = $get('field_group_fields');

                    if (! $fieldsData) {
                        return null;
                    }

                    $previewHtmlString = collect($fieldsData)
                        ->map(fn ($arr) => <<<Html
                            <div class="dark:ring-white/20 gap-1.5 grid grid-cols-3 lg:grid-cols-4 items-center mb-4 ring-1 ring-gray-900/10 rounded-md shadow-sm">
                                <span class="p-4 bg-gray-200 dark:!bg-gray-700 rounded-l-md">
                                    {$arr['type']}
                                </span>
                                <span class="p-4 col-span-2 lg:col-span-3 truncate">
                                    {$arr['label']}
                                </span>
                            </div>
                        Html)
                        ->implode('');

                    return new HtmlString($previewHtmlString);
                }),
        ];
    }

    protected static function getFieldGroupSortColumn()
    {
        return 'sort';
    }
}
