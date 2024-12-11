<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

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
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Pages;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Widgets;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
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
            ->schema([
                Forms\Components\Section::make()
                    ->heading(__('inspirecms::resources/document-type.general.section.heading'))
                    ->columns(1)
                    ->aside()
                    ->schema([
                        ...static::getCreateFormSchema(),
                    ]),
                Forms\Components\Section::make()
                    ->heading(__('inspirecms::resources/document-type.rejected.section.heading'))
                    ->columns(1)
                    ->aside()
                    ->schema([static::getRejectedRepeater()]),
            ]);
    }

    /** @return array */
    public static function getCreateFormSchema()
    {
        return [
            static::getSlugFormComponent()->inlineLabel(),
            static::getTitleFormComponent()->inlineLabel(),
            static::getShowChildAsTableFormComponent(),
            // static::getCategoryFormComponent(),
            static::getIconFormComponent()->inlineLabel(),
        ];
    }

    public static function createForm(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Section::make()
                    ->heading(__('inspirecms::resources/document-type.general.section.heading'))
                    ->columns(1)
                    ->aside()
                    ->schema(static::getCreateFormSchema()),
            ]);
    }

    /**
     * Replicates the given form.
     */
    public static function replicateForm(Form $form): Form
    {
        return $form->schema(static::getCreateFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('inspirecms::resources/document-type.empty_state.heading'))
            ->emptyStateDescription(__('inspirecms::resources/document-type.empty_state.description'))
            ->emptyStateActions([])
            // ->groups([
            //     Tables\Grouping\Group::make('category')
            //         ->label(__('inspirecms::resources/document-type.category.label'))
            //         ->getTitleFromRecordUsing(fn (Model | DocumentType $record) => $record->getCategoryEnum()?->getLabel())
            //         ->getDescriptionFromRecordUsing(fn (Model | DocumentType $record) => $record->getCategoryEnum()?->getDescription()),
            // ])
            // ->defaultGroup('category')
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
                    ->color(function (Model | DocumentType $record, $state) {
                        if ($record->getCategoryEnum()?->canManageChildDocumentTypes() === false) {
                            return 'gray';
                        }

                        return $state ? 'success' : 'danger';
                    })
                    ->icon(function (Model | DocumentType $record, $state) {
                        if ($record->getCategoryEnum()?->canManageChildDocumentTypes() === false) {
                            return 'heroicon-o-minus-circle';
                        }
                        if ($state) {
                            return FilamentIcon::resolve('tables::columns.icon-column.true')
                                ?? 'heroicon-o-check-circle';
                        }

                        return FilamentIcon::resolve('tables::columns.icon-column.false')
                            ?? 'heroicon-o-x-circle';
                    }),
                // Tables\Columns\TextColumn::make('category')
                //     ->label(__('inspirecms::resources/document-type.category.label'))
                //     ->badge()
                //     ->getStateUsing(fn (Model | DocumentType $record) => $record->getCategoryEnum())
                //     ->formatStateUsing(fn (?DocumentTypeCategory $state) => $state?->getLabel())
                //     ->color(fn (?DocumentTypeCategory $state) => $state?->getColor()),

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
                RelationManagers\RejectingDocumentTypesRelationManager::class,
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

    //region Global search
    public static function getGloballySearchableAttributes(): array
    {
        return ['slug'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return UIHelper::generateTextWithBadge(
            text: static::getRecordTitle($record),
            badgeText: $record instanceof DocumentType ? $record->slug : null,
            attibutes: [
                'text' => ['class' => 'flex-1 font-semibold'],
                'badge' => ['class' => 'font-mono'],
            ]
        );
    }
    //endregion Global search

    //region Form field(s)/component(s)
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
        return Forms\Components\Select::make('category')
            ->label(__('inspirecms::resources/document-type.category.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.show_as_table.category'))
            ->options(static::getModel()::getCategoryEnumClass())
            ->default(static::getModel()::getCategoryEnumClass()::getDefaultValue()->value)
            ->disabled(function ($operation) {
                if ($operation === 'edit') {
                    return true;

                }

                return false;
            })
            ->required()
            ->live()->helperText(function ($state) {
                if ($state) {
                    if ($enum = static::getModel()::getCategoryEnumClass()::tryFrom($state)) {
                        return $enum->getDescription();
                    }
                }

                return null;
            });
    }

    /** @return Forms\Components\Field | Forms\Components\Component*/
    protected static function getShowChildAsTableFormComponent()
    {
        return Forms\Components\Toggle::make('show_as_table')
            ->label(__('inspirecms::resources/document-type.show_as_table.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.show_as_table.validation_attribute'))
            ->inlineLabel()
            ->default(false)
            ->live()
            ->hidden(function ($get) {
                if ($enum = static::getModel()::getCategoryEnumClass()::tryFrom($get('category'))) {
                    return ! $enum->canManageChildDocumentTypes();
                }

                return false;
            });
    }

    /** @return Forms\Components\Field | Forms\Components\Component*/
    protected static function getRejectedRepeater()
    {
        return Forms\Components\Repeater::make('rejectedDocumentTypes')
            ->hiddenLabel()
            ->relationship('rejectedDocumentTypes')
            ->validationAttribute(__('inspirecms::resources/document-type.rejected_document_types.validation_attribute'))
            ->saveRelationshipsUsing(function (array $state, Model | DocumentType $record, Forms\Components\Repeater $component) {
                if (! is_array($state)) {
                    $state = [];
                }
                $recordIds = Arr::pluck($state, 'rejected_document_type_id') ?? [];
                /** @var BelongsToMany $relationship */
                $relationship = $component->getRelationship();
                $relationship->sync($recordIds);
            })
            ->defaultItems(0)
            ->reorderable(false)
            ->collapsible()->collapsed()
            ->itemLabel(fn (array $state): ?string => $state['title'] ?? $state['slug'] ?? null)
            ->extraItemActions([
                Forms\Components\Actions\Action::make('open')
                    ->icon(FilamentIcon::resolve('inspirecms::goto'))
                    ->label(__('filament-actions::edit.single.label'))
                    // Hide if url is empty
                    ->visible(fn (Forms\Components\Actions\Action $action) => filled($action->getUrl()))
                    ->url(function (array $arguments, Forms\Components\Repeater $component) {

                        $itemData = $component->getRawItemState($arguments['item']);

                        $recordId = $itemData['rejected_document_type_id'] ?? null;

                        if (blank($recordId)) {
                            return null;
                        }

                        return FilamentResourceHelper::attemptToGetUrl(static::class, 'edit', ['record' => $recordId], false);

                    }, true),
            ])
            ->addAction(
                fn (Forms\Components\Actions\Action $action) => $action
                    ->label(__('inspirecms::actions.attach.label'))
                    ->size('lg')
                    ->extraAttributes(['class' => 'w-full'])
                    ->icon(FilamentIcon::resolve('inspirecms::attach'))
                    ->slideOver()
                    ->form(fn (Form $form, Forms\Components\Repeater $component, $state, null | Model | DocumentType $record) => $form
                        ->schema(function () use ($component, $state, $record): array {

                            /** @var BelongsToMany $relationship */
                            $relationship = $component->getRelationship();
                            $inverseRelationshipName = 'rejectingDocumentTypes';

                            $getOptions = static function (int $optionsLimit, ?string $search = null, array $searchColumns = []) use ($relationship, $inverseRelationshipName, $state, $record): array {

                                $excepts = collect($state ?? [])->pluck('document_type_id')->merge([$record?->getKey()])->filter()->unique()->values()->all();

                                return collect(
                                        SearchHelper::getAttachOptions(
                                            relationship: $relationship, 
                                            inverseRelationshipName: $inverseRelationshipName, 
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
                                            attibutes: [
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
                                    ->options(fn (Forms\Components\Select $component): array => $getOptions(optionsLimit: $component->getOptionsLimit(), searchColumns: $component->getSearchColumns()))
                            ];
                        })
                    )
                    ->action(function (array $data, Forms\Components\Repeater $component) {
                        $recordIds = $data['recordId'] ?? [];
                        if (! is_array($recordIds)) {
                            $recordIds = [$recordIds];
                        }
                        /** @var BelongsToMany $relationship */
                        $relationship = $component->getRelationship();
                        /** @var Collection<Model> */
                        $rejectedDocumentTypes = $relationship->getRelated()->find($recordIds);

                        foreach ($rejectedDocumentTypes as $rejectedDocumentType) {

                            $data = [
                                'rejected_document_type_id' => $rejectedDocumentType->getKey(),
                                'title' => $rejectedDocumentType->title,
                                'slug' => $rejectedDocumentType->slug,
                            ];

                            $newUuid = $component->generateUuid();
    
                            $items = $component->getState();
    
                            if ($newUuid) {
                                $items[$newUuid] = $data;
                            } else {
                                $items[] = $data;
                            }
    
                            $component->state($items);
    
                            $component->getChildComponentContainer($newUuid ?? array_key_last($items))->fill($data);
    
                            $component->collapsed(true, shouldMakeComponentCollapsible: true);
    
                        }

                        $component->callAfterStateUpdated();
                    })
            )
            ->columns(2)
            ->schema([
                Forms\Components\Hidden::make('rejected_document_type_id')->dehydratedWhenHidden(),
                Forms\Components\TextInput::make('slug')->label(__('inspirecms::resources/document-type.slug.label'))->inlineLabel()->disabled(),
                Forms\Components\TextInput::make('title')->label(__('inspirecms::resources/document-type.title.label'))->inlineLabel()->disabled(),
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
    //endregion Form field(s)/component(s)
}
