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
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Base\Enums\Interfaces\DocumentTypeCategory;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Pages;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Widgets;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\TimestampsGroup;
use SolutionForest\InspireCms\Filament\Resources\Helpers\DocumentTypeResourceHelper;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
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
                    ->heading(__('inspirecms::resources/document-type.general.section.label'))
                    ->columns(1)
                    ->aside()
                    ->schema([
                        ... static::getCreateFormSchema(),
                        static::getDisplayParentFormComponent()->inlineLabel(),
                    ]),
                Forms\Components\Section::make()
                    ->heading(__('inspirecms::resources/document-type.children.section.label'))
                    ->columns(1)
                    ->aside()
                    ->visible(function (Model | DocumentType | null $record) {
                        if (! $record) {
                            return false;
                        }

                        return $record->canBeParent();
                    })
                    ->schema(fn ($record) => array_filter([
                        static::getChildrenRepeater($record),
                    ])),
            ]);
    }

    /** @return array */
    public static function getCreateFormSchema()
    {
        return [
            static::getSlugFormComponent()->inlineLabel(),
            static::getTitleFormComponent()->inlineLabel(),
            static::getShowChildAsTableFormComponent(),
            // static::getTypeFormComponent($parent),
            static::getIconFormComponent()->inlineLabel(),
        ];
    }

    public static function createForm(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Section::make()
                    ->heading(__('inspirecms::resources/document-type.general.section.label'))
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
            ->emptyStateActions([])
            // ->groups([
            //     Tables\Grouping\Group::make('category')
            //         ->label(__('inspirecms::resources/document-type.category.label'))
            //         ->getTitleFromRecordUsing(fn (DocumentType $record) => $record->getCategoryEnum()?->getLabel())
            //         ->getDescriptionFromRecordUsing(fn (DocumentType $record) => $record->getCategoryEnum()?->getDescription()),
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
                Tables\Columns\ColumnGroup::make(__('inspirecms::inspirecms.parent'), [
                    Tables\Columns\TextColumn::make('parent.title')
                        ->label(__('inspirecms::resources/document-type.title.label')),
                    Tables\Columns\TextColumn::make('parent.slug')
                        ->label(__('inspirecms::resources/document-type.slug.label'))
                        ->badge(),
                ]),
                Tables\Columns\IconColumn::make('show_children_as_table')
                    ->label(__('inspirecms::resources/document-type.show_children_as_table.label'))
                    ->color(function (DocumentType $record, $state) {
                        if ($record->getCategoryEnum()?->canManageChildDocumentTypes() === false) {
                            return 'gray';
                        }

                        return $state ? 'success' : 'danger';
                    })
                    ->icon(function (DocumentType $record, $state) {
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
                //     ->getStateUsing(fn (DocumentType $record) => $record->getCategoryEnum())
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
                Tables\Filters\TernaryFilter::make('is_root')
                    ->label(__('inspirecms::resources/document-type.is_root.label'))
                    // ->default(true)
                    ->queries(
                        true: fn ($query) => $query->whereIsRoot(condition: true),
                        false: fn ($query) => $query->whereIsRoot(condition: false),
                        blank: fn ($query) => $query,
                    )
                    ->hiddenOn([RelationManagers\ChildrenRelationManager::class]),
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
            ->with(['parent'])
            ->withCount(['templates', 'fieldGroups', 'children']);
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
            badgeText: $record->slug,
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
    protected static function getDisplayParentFormComponent()
    {
        return Forms\Components\Placeholder::make('display_parent')
            ->label(__('inspirecms::inspirecms.parent'))
            ->visible(function ($operation, ?DocumentType $record) {
                if ($operation === 'create') {
                    return false;
                }

                return $record?->canHaveParent() ?? false;
            })
            ->content(function ($record) {

                $parent = $record?->parent;

                if (! $parent) {
                    return null;
                }

                $url = FilamentResourceHelper::attemptToGetUrl(static::class, ['edit', 'view'], ['record' => $parent], true);

                $title = static::getRecordTitle($parent);
                $slug = $parent->slug;

                $text = "{$title} ({$slug})";

                if (! $url) {
                    return $text;
                }

                return UIHelper::generateTextWithIconButton($text, FilamentIcon::resolve('inspirecms::goto'), 'gray', 'sm', 'mr-2', $url);
            });
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTitleFormComponent()
    {
        return Forms\Components\TextInput::make('title')
            ->label(__('inspirecms::resources/document-type.title.label'))
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getSlugFormComponent()
    {
        return Forms\Components\TextInput::make('slug')
            ->label(__('inspirecms::resources/document-type.slug.label'))
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
     * @param  DocumentType|Model|null  $parent
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTypeFormComponent($parent = null)
    {
        return Forms\Components\Select::make('category')
            ->label(__('inspirecms::resources/document-type.category.label'))
            ->options(static::getModel()::getCategoryEnumClass())
            ->default(static::getModel()::getCategoryEnumClass()::getDefaultValue()->value)
            ->disabled(function ($operation) use ($parent) {
                if ($operation === 'edit') {
                    return true;

                }
                // If create with parent and parent can have children, disable this field
                elseif ($operation === 'create' && ! is_null($parent) && $parent->canBeParent()) {

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
        return Forms\Components\Toggle::make('show_children_as_table')
            ->label(__('inspirecms::resources/document-type.show_children_as_table.label'))
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
    protected static function getChildrenRepeater($record)
    {
        return Forms\Components\Repeater::make('children')
            ->hiddenLabel()
            ->relationship('children')
            ->defaultItems(0)
            ->reorderable(false)
            ->collapsible()->collapsed(function (?Forms\ComponentContainer $item) {

                $itemStatePath = $item->getStatePath(false);

                // If item is new, do not collapse
                if (Str::startsWith($itemStatePath, 'record-')) {
                    return true;
                }
                
                return false;
            })
            ->itemLabel(fn (array $state): ?string => $state['title'] ?? $state['slug'] ?? null)
            // Cannot display delete button if it exists
            ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action
                ->hidden(function (array $arguments, Forms\Components\Repeater $component) {
                    
                    if (! isset($arguments['item'])) {
                        return false;
                    }

                    $itemData = $component->getRawItemState($arguments['item']);

                    // cannot delete if it created (has primary key)
                    return filled($itemData['id']);
                })
            )
            ->extraItemActions([
                Forms\Components\Actions\Action::make('open')
                    ->icon(FilamentIcon::resolve('inspirecms::goto'))
                    ->label(__('filament-actions::edit.single.label'))
                    // Hide if url is empty
                    ->visible(fn (Forms\Components\Actions\Action $action) => filled($action->getUrl()))
                    ->url(function (array $arguments, Forms\Components\Repeater $component) {

                        $itemData = $component->getRawItemState($arguments['item']);

                        $recordId = $itemData['id'];

                        if (blank($recordId)) {
                            return null;
                        }

                        return FilamentResourceHelper::attemptToGetUrl(static::class, 'edit', ['record' => $recordId], false);
                        
                    }, true),
            ])
            ->addAction(fn (Forms\Components\Actions\Action $action) => $action
                ->label(__('inspirecms::inspirecms.add'))
                ->size('lg')
                ->extraAttributes(['class' => 'w-full'])
                ->icon(FilamentIcon::resolve('inspirecms::add'))
            )
            ->schema([
                Forms\Components\Hidden::make('id'),
                ...Arr::map(static::getCreateFormSchema(), fn (Forms\Components\Component$component) => $component
                    // Disable if id exists (record is created)
                    ->disabled(fn (Forms\Get $get) => filled($get('id')))
                ),
            ]);
    }

    /** @return Forms\Components\Field | Forms\Components\Component*/
    protected static function getIconFormComponent()
    {
        return \Guava\FilamentIconPicker\Forms\IconPicker::make('icon')
            ->label(__('inspirecms::resources/document-type.icon.label'))
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
