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
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Base\Enums\Interfaces\DocumentTypeCategory;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Pages;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Widgets;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\TimestampsGroup;
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
        ];
    }

    protected static ?int $navigationSort = -10;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Settings::class;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->columns(1)
                    ->columnSpan(2)
                    ->schema([
                        Forms\Components\Section::make()
                            ->hiddenOn(['create'])
                            ->schema([
                                static::getDisplayIdFormComponent()->inlineLabel(),
                                static::getDisplayParentFormComponent()->inlineLabel(),
                            ]),
                        Forms\Components\Section::make()
                            ->schema([
                                static::getParentIdFormComponent(),
                                static::getTitleFormComponent()->inlineLabel()->columnSpanFull(),
                                static::getSlugFormComponent()->inlineLabel()->columnSpanFull(),
                            ]),
                    ]),
                Forms\Components\Section::make()
                    ->columns(1)
                    ->columnSpan(1)
                    ->schema([
                        static::getTypeFormComponent(),
                        static::getShowChildAsTableFormComponent(),
                        static::getTimestampsGroupedFormComponent(),
                    ]),
            ]);
    }

    /**
     * Used to define the form for the children relation manager.
     */
    public static function childrenForm(Form $form, $parent): Form
    {
        return $form
            ->columns(3)
            ->schema([

                Forms\Components\Group::make()
                    ->columns(1)
                    ->columnSpan(2)
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                static::getParentIdFormComponent($parent),
                                static::getTitleFormComponent()->inlineLabel()->columnSpanFull(),
                                static::getSlugFormComponent()->inlineLabel()->columnSpanFull(),
                            ]),
                    ]),
                Forms\Components\Section::make()
                    ->columns(1)
                    ->columnSpan(1)
                    ->schema([
                        static::getTypeFormComponent($parent),
                        static::getShowChildAsTableFormComponent(),
                        static::getTimestampsGroupedFormComponent(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateActions([])
            ->groups([
                Tables\Grouping\Group::make('category')
                    ->label(__('inspirecms::resources/document-type.category.label'))
                    ->getTitleFromRecordUsing(fn (DocumentType $record) => $record->getCategoryEnum()?->getLabel())
                    ->getDescriptionFromRecordUsing(fn (DocumentType $record) => $record->getCategoryEnum()?->getDescription()),
            ])
            ->defaultGroup('category')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
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
                Tables\Columns\TextColumn::make('category')
                    ->label(__('inspirecms::resources/document-type.category.label'))
                    ->badge()
                    ->getStateUsing(fn (DocumentType $record) => $record->getCategoryEnum())
                    ->formatStateUsing(fn (?DocumentTypeCategory $state) => $state?->getLabel())
                    ->color(fn (?DocumentTypeCategory $state) => $state?->getColor()),

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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->iconButton(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_root')
                    ->label(__('inspirecms::resources/document-type.is_root.label'))
                    ->default(true)
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
            'create' => Pages\CreateDocumentType::route('/create'),
            'edit' => Pages\EditDocumentType::route('/{record}/edit'),
            'view' => Pages\ViewDocumentType::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'field_group' => RelationGroup::make(fn () => __('inspirecms::resources/document-type.field_groups.label'), [
                RelationManagers\InheritedDocumentTypesRelationManager::class,
                RelationManagers\FieldGroupsRelationManager::class,
            ])->badge(function ($ownerRecord) {
                if (is_null($ownerRecord->field_groups_count)) {
                    $ownerRecord->loadCount('fieldGroups');
                }

                return $ownerRecord->field_groups_count;
            }),
            'children' => RelationManagers\ChildrenRelationManager::class,
            'templates' => RelationManagers\TemplatesRelationManager::class,
            'referenced_by' => RelationGroup::make(fn () => __('inspirecms::inspirecms.referenced_by'), [
                RelationManagers\ContentRelationManager::class,
                RelationManagers\InheritingDocumentTypesRelationManager::class,
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
    protected static function getDisplayIdFormComponent()
    {
        return Forms\Components\Placeholder::make('display_id')
            ->label(__('inspirecms::inspirecms.id'))
            ->hiddenOn(['create'])
            ->content(fn ($record) => $record?->getKey());
    }

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
            ->live(true, 300)->afterStateUpdated(function ($state, $get, $set, $operation) {
                // Fill slug if empty / operation is create
                if ($operation === 'create' || empty($get('slug'))) {
                    $set('slug', Str::slug($state));
                }
            })
            ->autofocus()
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getSlugFormComponent()
    {
        return Forms\Components\TextInput::make('slug')
            ->label(__('inspirecms::resources/document-type.slug.label'))
            ->live(true, 300)->afterStateUpdated(fn ($component, $state) => $component->state(Str::slug($state)))
            ->unique(table: static::getModel(), column: 'slug', ignoreRecord: true)
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

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTimestampsGroupedFormComponent()
    {
        return TimestampsGroup::make()
            ->columns(['default' => 1]);
    }

    /**
     * @param  DocumentType|Model|null  $parent
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getParentIdFormComponent($parent = null)
    {
        return Forms\Components\Hidden::make('parent_id')
            ->dehydratedWhenHidden()
            ->afterStateHydrated(function ($operation, $state, $component) use ($parent) {

                if ($operation === 'create') {

                    if ($parent?->canBeParent() ?? false) {
                        $component->state($parent->getKey());

                        return;
                    }
                }

                $component->state($state);

            });
    }
    //endregion Form field(s)/component(s)
}
