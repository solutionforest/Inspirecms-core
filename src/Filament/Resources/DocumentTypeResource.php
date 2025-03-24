<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Base\Enums\Interfaces\DocumentTypeCategory;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\Pages;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\RelationManagers;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\Widgets;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Helpers\SearchHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;

class DocumentTypeResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'attach',
            'detach',
            'replicate',
        ];
    }

    protected static ?int $navigationSort = -10;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Settings::class;

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return FilamentIcon::resolve('inspirecms::document-type');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema(static::getBaseFormSchema(operation: 'edit'));
    }

    public static function getBaseFormSchema($operation = 'create'): array
    {
        return [
            Forms\Components\Section::make()
                ->heading(__('inspirecms::resources/document-type.general.section.heading'))
                ->columns(1)
                ->aside()
                ->schema([
                    static::getSlugFormComponent()->inlineLabel(),
                    static::getTitleFormComponent()->inlineLabel(),
                    static::getShowAsTableFormComponent(),
                    static::getCategoryFormComponent()->inlineLabel(),
                    static::getIconFormComponent()->inlineLabel(),
                ]),
            Forms\Components\Section::make()
                ->heading(__('inspirecms::resources/document-type.display.section.heading'))
                ->description(__('inspirecms::resources/document-type.display.section.description'))
                ->columns(1)
                ->aside()
                ->schema(
                    collect([
                        static::getShowAtRootFormComponent(),
                    ])
                        ->when($operation == 'edit', fn ($collection) => $collection->push(static::getAllowedRepeater()))
                        ->all()
                ),
        ];
    }

    public static function createForm(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema(static::getBaseFormSchema('create'));
    }

    /**
     * Replicates the given form.
     */
    public static function replicateForm(Form $form): Form
    {
        return $form->schema(static::getBaseFormSchema('replicate'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('inspirecms::resources/document-type.empty_state.heading'))
            ->emptyStateDescription(__('inspirecms::resources/document-type.empty_state.description'))
            ->emptyStateActions([])
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
                Tables\Columns\ViewColumn::make('icon')
                    ->view('inspirecms::filament.tables.columns.guava-icon')
                    ->label(__('inspirecms::resources/document-type.icon.label'))
                    ->extraAttributes(['class' => 'text-gray-500 dark:text-gray-400'])
                    ->width('1%'),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::resources/document-type.title.label')),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('inspirecms::resources/document-type.slug.label'))
                    ->sortable()
                    ->badge(),
                Tables\Columns\IconColumn::make('show_as_table')
                    ->label(__('inspirecms::resources/document-type.show_as_table.label'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('show_at_root')
                    ->label(__('inspirecms::resources/document-type.show_at_root.label'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('display_category')
                    ->label(__('inspirecms::resources/document-type.category.label'))
                    ->badge(),

                // timestamps
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->sortable()
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('inspirecms::inspirecms.last_updated_at'))
                    ->sortable()
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\ReplicateAction::make()
                    ->iconButton()
                    ->form(fn (Form $form) => static::replicateForm($form))
                    ->excludeAttributes(['templates_count', 'field_groups_count', 'children_count'])
                    ->after(function (Model | DocumentType $replica, Model | DocumentType $record) {

                        $fieldGroups = $record->fieldGroups()->pluck($record->fieldGroups()->getQualifiedRelatedKeyName())->toArray();

                        $replica->fieldGroups()->sync($fieldGroups);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->iconButton(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('show_as_table')
                    ->label(__('inspirecms::resources/document-type.show_as_table.label')),
                Tables\Filters\TernaryFilter::make('show_at_root')
                    ->label(__('inspirecms::resources/document-type.show_at_root.label')),
                Tables\Filters\SelectFilter::make('category')
                    ->multiple()
                    ->label(__('inspirecms::resources/document-type.category.label'))
                    ->options(static::getModel()::getCategoryEnumClass()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentTypes::route('/'),
            'edit' => Pages\EditDocumentType::route('/{record}/edit'),
            'view' => Pages\ViewDocumentType::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'field_group' => RelationGroup::make(fn () => __('inspirecms::resources/document-type.field_groups.tab.label'), [
                // RelationManagers\InheritedDocumentTypesRelationManager::class,
                RelationManagers\FieldGroupsRelationManager::class,
            ])->icon(FilamentIcon::resolve('inspirecms::fields')),
            'templates' => RelationGroup::make(fn () => __('inspirecms::resources/document-type.templates.tab.label'), [
                RelationManagers\TemplatesRelationManager::class,
            ])->icon(FilamentIcon::resolve('inspirecms::templates')),
            'used_by' => RelationGroup::make(fn () => __('inspirecms::inspirecms.used_by'), [
                RelationManagers\ContentRelationManager::class,
                RelationManagers\AllowingDocumentTypesRelationManager::class,
                // RelationManagers\InheritingDocumentTypesRelationManager::class,
            ]),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\AlertOverview::make(),
        ];
    }

    /**
     * @return class-string<Model & DocumentType>
     */
    public static function getModel(): string
    {
        return InspireCmsConfig::getDocumentTypeModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.document_type');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['templates', 'fieldGroups']);
    }

    public static function canDelete(Model $record): bool
    {
        if ($record instanceof DocumentType) {
            return ! ($record->content()->withoutGlobalScopes([\Illuminate\Database\Eloquent\SoftDeletingScope::class])->count() > 0);
        }

        return parent::canDelete($record);
    }

    // region Global search
    public static function getGloballySearchableAttributes(): array
    {
        return ['slug'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return UIHelper::generateTextWithBadge(
            text: static::getRecordTitle($record),
            badgeText: $record instanceof DocumentType ? $record->slug : null,
            attributes: [
                'text' => ['class' => 'flex-1 font-semibold'],
                'badge' => ['class' => 'font-mono'],
            ]
        );
    }
    // endregion Global search

    // region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTitleFormComponent()
    {
        return Forms\Components\TextInput::make('title')
            ->label(__('inspirecms::resources/document-type.title.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.title.category'))
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getSlugFormComponent()
    {
        return Forms\Components\TextInput::make('slug')
            ->label(__('inspirecms::resources/document-type.slug.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.slug.category'))
            ->live(true, 300)
            ->afterStateUpdated(function ($component, $state, Forms\Get $get, Forms\Set $set, $operation) {
                $component->state(Str::slug($state));
                // Fill slug if empty / operation is create
                if ($operation === 'create' || empty($get('title'))) {
                    $set('title', $state);
                }
            })
            ->unique(table: static::getModel(), column: 'slug', ignoreRecord: true)
            ->autofocus()
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getCategoryFormComponent()
    {
        $enumClass = static::getModel()::getCategoryEnumClass();

        return Forms\Components\ToggleButtons::make('category')
            ->label(__('inspirecms::resources/document-type.category.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.category.validation_attribute'))
            ->inline()
            ->grouped()
            ->options($enumClass)
            ->default($enumClass::getDefaultValue()->value)
            ->required()
            ->live()
            ->colors(collect($enumClass::cases())->mapWithKeys(fn (DocumentTypeCategory $enumClass): array => [$enumClass->value => $enumClass->getColor()])->all())
            ->helperText(function ($state) use ($enumClass) {
                if ($state && ($enum = $enumClass::tryFrom($state))) {
                    return $enum->getDescription();
                }

                return null;
            });
    }

    /** @return Forms\Components\Field | Forms\Components\Component*/
    protected static function getShowAtRootFormComponent()
    {
        return Forms\Components\Toggle::make('show_at_root')
            ->label(__('inspirecms::resources/document-type.show_at_root.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.show_at_root.validation_attribute'))
            ->inlineLabel()
            ->default(true);
    }

    /** @return Forms\Components\Field | Forms\Components\Component*/
    protected static function getShowAsTableFormComponent()
    {
        return Forms\Components\Toggle::make('show_as_table')
            ->label(__('inspirecms::resources/document-type.show_as_table.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.show_as_table.validation_attribute'))
            ->inlineLabel()
            ->default(false)
            ->live();
    }

    /** @return Forms\Components\Field | Forms\Components\Component*/
    protected static function getAllowedRepeater()
    {
        $getAllowedDocumentTypesForSync = function (array $ids, Forms\Components\Repeater $component): Collection {

            /** @var BelongsToMany $relationship */
            $relationship = $component->getRelationship();

            return $relationship->getRelated()->find($ids);

        };

        $buildStateDocumentTypeToRepeater = function (string $action, Collection $documentTypes, Forms\Components\Repeater $component) {

            $isDetaching = $action == 'detach';

            $newState = $isDetaching ? [] : $component->getState();

            if ($documentTypes->isEmpty() && $isDetaching) {

                $component->state([]);

            } else {

                foreach ($documentTypes as $model) {

                    // Do nothing if the document type is already in the repeater
                    if (in_array($model->getKey(), Arr::pluck($newState, 'allowed_id'))) {
                        return;
                    }

                    $data = [
                        'allowed_id' => $model->getKey(),
                        'title' => $model->title,
                        'slug' => $model->slug,
                    ];

                    $newUuid = $component->generateUuid();
                    $newState[$newUuid] = $data;

                    $component->state($newState);

                    $component->getChildComponentContainer($newUuid ?? array_key_last($newState))->fill($data);
                }
            }

            // $component->collapsed(true, shouldMakeComponentCollapsible: true);

            $component->callAfterStateUpdated();

            return $newState;
        };

        $handleAttachRepeaterData = function ($idsToAttach, Forms\Components\Repeater $component) use ($getAllowedDocumentTypesForSync, $buildStateDocumentTypeToRepeater) {

            if (! is_array($idsToAttach)) {
                $idsToAttach = [$idsToAttach];
            }

            /** @var Collection<Model> */
            $allowedDocumentTypes = $getAllowedDocumentTypesForSync($idsToAttach, $component);

            $buildStateDocumentTypeToRepeater('attach', $allowedDocumentTypes, $component);

        };

        $handleSyncRepeaterData = function ($ids, Forms\Components\Repeater $component) use ($getAllowedDocumentTypesForSync, $buildStateDocumentTypeToRepeater) {

            /** @var Collection<Model> */
            $allowedDocumentTypes = empty($ids) ? collect() : $getAllowedDocumentTypesForSync($ids, $component);

            $buildStateDocumentTypeToRepeater('detach', $allowedDocumentTypes, $component);

        };

        return Forms\Components\Repeater::make('allowedDocumentTypes')
            ->relationship('allowedDocumentTypes')
            ->label(__('inspirecms::resources/document-type.allowed_document_types.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.allowed_document_types.validation_attribute'))
            ->saveRelationshipsUsing(function (array $state, Model | DocumentType $record, Forms\Components\Repeater $component) {
                if (! is_array($state)) {
                    $state = [];
                }
                $recordIds = Arr::pluck($state, 'allowed_id') ?? [];
                /** @var BelongsToMany $relationship */
                $relationship = $component->getRelationship();
                $relationship->sync($recordIds);
            })
            ->defaultItems(0)
            ->reorderable(false)
            // ->itemLabel(fn (array $state): ?string => $state['title'] ?? $state['slug'] ?? null)
            ->extraItemActions([
                Forms\Components\Actions\Action::make('open')
                    ->icon(FilamentIcon::resolve('inspirecms::goto'))
                    ->label(__('filament-actions::edit.single.label'))
                    // Hide if url is empty
                    ->visible(fn (Forms\Components\Actions\Action $action) => filled($action->getUrl()))
                    ->url(function (array $arguments, Forms\Components\Repeater $component) {

                        $itemData = $component->getRawItemState($arguments['item']);

                        $recordId = $itemData['allowed_id'] ?? null;

                        if (blank($recordId)) {
                            return null;
                        }

                        return FilamentResourceHelper::attemptToGetUrl(static::class, 'edit', ['record' => $recordId], false);

                    }, true),
            ])
            ->hintAction(
                Forms\Components\Actions\Action::make('bulkDetach')
                    ->icon(FilamentIcon::resolve('inspirecms::detach'))
                    // todo: add translation label
                    ->color('danger')
                    ->slideOver()
                    ->modalSubmitAction(fn ($action) => $action->color('primary'))
                    ->form(function (Form $form, Forms\Components\Repeater $component) {
                        $state = $component->getState();
                        $repeaterState = collect($state)->values()->where(fn ($item) => is_array($item));
                        $options = $repeaterState->pluck('title', 'allowed_id')->all();
                        $descriptions = $repeaterState->pluck('slug', 'allowed_id')->all();

                        return $form
                            ->schema([
                                Forms\Components\CheckboxList::make('records')
                                    ->hiddenLabel()
                                    ->gridDirection('row')
                                    ->searchable()
                                    ->options($options)
                                    ->descriptions($descriptions)
                                    ->bulkToggleable()
                                    ->columns(4)
                                    ->afterStateHydrated(function ($component) use ($options) {
                                        $component->state(array_keys($options));
                                    }),
                            ]);
                    })
                    ->action(fn (array $data, Forms\Components\Repeater $component) => $handleSyncRepeaterData($data['records'] ?? [], $component)),
            )
            ->addAction(
                fn (Forms\Components\Actions\Action $action) => $action
                    ->label(__('inspirecms::buttons.attach.label'))
                    ->size('lg')
                    ->extraAttributes(['class' => 'w-full'])
                    ->icon(FilamentIcon::resolve('inspirecms::attach'))
                    ->slideOver()
                    ->form(
                        fn (Form $form, Forms\Components\Repeater $component, $state, null | Model | DocumentType $record) => $form
                            ->schema(function () use ($component, $state, $record): array {

                                /** @var BelongsToMany $relationship */
                                $relationship = $component->getRelationship();

                                $getOptions = static function (int $optionsLimit, ?string $search = null, array $searchColumns = []) use ($relationship, $state, $record): array {

                                    $excepts = collect($state ?? [])
                                        ->pluck('allowed_id')   // existing state's id
                                        ->merge([$record?->getKey()]) // current record's id
                                        ->filter()->unique()->values()
                                        ->all();

                                    return collect(
                                        SearchHelper::getAttachOptionsIgnoringInverse(
                                            relationship: $relationship,
                                            optionsLimit: $optionsLimit,
                                            getRecordTitleUsing: fn (Model | DocumentType $record) => [$record->title, $record->slug],
                                            search: $search,
                                            searchColumns: $searchColumns,
                                            excepts: $excepts,
                                        )
                                    )
                                        ->map(function (array $values) {
                                            [$title, $slug] = $values;

                                            return UIHelper::generateTextWithBadge(
                                                text: $title,
                                                badgeText: $slug,
                                                attributes: [
                                                    'text' => ['class' => 'flex-1 font-semibold'],
                                                    'badge' => ['class' => 'font-mono'],
                                                ]
                                            )->toHtml();
                                        })
                                        ->all();

                                };

                                return [
                                    Forms\Components\Select::make('recordId')
                                        ->hiddenLabel()
                                        ->searchable(['title', 'slug'])
                                        ->allowHtml()
                                        ->multiple()
                                        ->getSearchResultsUsing(static fn (Forms\Components\Select $component, string $search): array => $getOptions(optionsLimit: $component->getOptionsLimit(), search: $search, searchColumns: $component->getSearchColumns()))
                                        ->options(fn (Forms\Components\Select $component): array => $getOptions(optionsLimit: $component->getOptionsLimit(), searchColumns: $component->getSearchColumns())),
                                ];
                            })
                    )
                    ->action(fn (array $data, Forms\Components\Repeater $component) => $handleAttachRepeaterData($data['recordId'] ?? [], $component))
            )
            ->columns(1)
            ->schema([
                Forms\Components\Hidden::make('allowed_id')->dehydratedWhenHidden(),
                Forms\Components\Placeholder::make('title')
                    ->label(__('inspirecms::inspirecms.document_type'))
                    ->inlineLabel()
                    ->content(function ($get) {
                        $slug = $get('slug');
                        $title = $get('title');

                        return UIHelper::generateTextWithDescription(
                            text: $title ?? __('inspirecms::inspirecms.n/a'),
                            description: $slug ?? __('inspirecms::inspirecms.n/a'),
                        );
                    }),
            ]);
    }

    /** @return Forms\Components\Field | Forms\Components\Component*/
    protected static function getIconFormComponent()
    {
        return \Guava\FilamentIconPicker\Forms\IconPicker::make('icon')
            ->label(__('inspirecms::resources/document-type.icon.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.icon.validation_attribute'))
            ->preload()
            ->columns([
                'default' => 5,
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
            ])
            ->extraAttributes(['class' => 'w-1/2']);
    }
    // endregion Form field(s)/component(s)
}
