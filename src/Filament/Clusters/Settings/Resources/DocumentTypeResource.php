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
use SolutionForest\InspireCms\Base\Enums\Interfaces\DocumentTypeType;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Contracts\DocumentTypeForm;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Pages;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\TimestampsGroup;
use SolutionForest\InspireCms\Filament\Tables\Actions\QuickEditAction;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

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

    public static function quickForm(Form $form): Form
    {
        return $form
            ->schema([
                static::getDisplayIdFormComponent()->inlineLabel(),
                static::getDisplayParentFormComponent()->inlineLabel(),
                static::getTitleFormComponent()->inlineLabel(),
                static::getSlugFormComponent()->inlineLabel(),
                static::getTypeFormComponent()->inlineLabel(),
                static::getShowChildAsTableFormComponent(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateActions([])
            ->groups([
                Tables\Grouping\Group::make('type')
                    ->label(__('inspirecms::inspirecms.type'))
                    ->getTitleFromRecordUsing(fn (DocumentType $record) => $record->getTypeEnum()?->getLabel())
                    ->getDescriptionFromRecordUsing(fn (DocumentType $record) => $record->getTypeEnum()?->getDescription()),
            ])
            ->defaultGroup('type')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title')),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('inspirecms::inspirecms.slug'))
                    ->sortable()
                    ->badge(),
                Tables\Columns\ColumnGroup::make(__('inspirecms::inspirecms.parent'), [
                    Tables\Columns\TextColumn::make('parent.title')
                        ->label(__('inspirecms::inspirecms.title')),
                    Tables\Columns\TextColumn::make('parent.slug')
                        ->label(__('inspirecms::inspirecms.slug'))
                        ->badge(),
                ]),
                Tables\Columns\IconColumn::make('show_children_as_table')
                    ->label(__('inspirecms::inspirecms.show_children_as_table'))
                    ->color(function (DocumentType $record, $state) {
                        if ($record->getTypeEnum()?->canManageChildDocumentTypes() === false) {
                            return 'gray';
                        }

                        return $state ? 'success' : 'danger';
                    })
                    ->icon(function (DocumentType $record, $state) {
                        if ($record->getTypeEnum()?->canManageChildDocumentTypes() === false) {
                            return 'heroicon-o-minus-circle';
                        }
                        if ($state) {
                            return FilamentIcon::resolve('tables::columns.icon-column.true')
                                ?? 'heroicon-o-check-circle';
                        }

                        return FilamentIcon::resolve('tables::columns.icon-column.false')
                            ?? 'heroicon-o-x-circle';
                    }),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('inspirecms::inspirecms.type'))
                    ->badge()
                    ->getStateUsing(fn (DocumentType $record) => $record->getTypeEnum())
                    ->formatStateUsing(fn (?DocumentTypeType $state) => $state?->getLabel())
                    ->color(fn (?DocumentTypeType $state) => $state?->getColor()),

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
                Tables\Actions\ActionGroup::make([
                    QuickEditAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->iconButton(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_root')
                    ->label(__('inspirecms::inspirecms.is_root'))
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
            RelationGroup::make(fn () => __('inspirecms::inspirecms.field_group'), [
                RelationManagers\InheritedDocumentTypesRelationManager::class,
                RelationManagers\FieldGroupsRelationManager::class,
            ]),
            RelationManagers\ChildrenRelationManager::class,
            RelationManagers\TemplatesRelationManager::class,
            RelationGroup::make(fn () => __('inspirecms::inspirecms.referenced_by'), [
                RelationManagers\ContentRelationManager::class,
                RelationManagers\InheritingDocumentTypesRelationManager::class,
            ]),
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
            ->with('parent');
    }

    //region Global search
    public static function getGloballySearchableAttributes(): array
    {
        return ['slug'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return UIHelper::generateTextWithBadge(static::getRecordTitle($record), $record->slug);
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
            ->hiddenOn(['create', 'quick_create'])
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
                if ($operation === 'create' || $operation === 'quick_create') {
                    return false;
                }

                return $record?->canHaveParent() ?? false;
            })
            ->content(function ($livewire, $record) {
                if ($livewire instanceof DocumentTypeForm) {
                    $parent = $livewire->getParent();
                } else {
                    $parent = $record?->parent;
                }

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
            ->label(__('inspirecms::inspirecms.title'))
            ->live(true, 300)->afterStateUpdated(function ($state, $get, $set, $operation) {
                // Fill slug if empty / operation is create
                if ($operation === 'create' || $operation === 'quick_create' || empty($get('slug'))) {
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
            ->label(__('inspirecms::inspirecms.slug'))
            ->live(true, 300)->afterStateUpdated(fn ($component, $state) => $component->state(Str::slug($state)))
            ->unique(table: static::getModel(), column: 'slug', ignoreRecord: true)
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTypeFormComponent()
    {
        return Forms\Components\Select::make('type')
            ->label(__('inspirecms::inspirecms.type'))
            ->options(static::getModel()::getTypeEnumClass())
            ->default(static::getModel()::getTypeEnumClass()::getDefaultValue()->value)
            ->disabled(function ($operation, $livewire) {
                if ($operation === 'edit' || $operation === 'quick_edit') {
                    return true;
                } elseif ($operation === 'create' && $livewire instanceof DocumentTypeForm) {

                    return $livewire->canBeParent($livewire->getParentKey());
                }

                return false;
            })
            ->required()
            ->live()->helperText(function ($state) {
                if ($state) {
                    if ($enum = static::getModel()::getTypeEnumClass()::tryFrom($state)) {
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
            ->label(__('inspirecms::inspirecms.show_children_as_table'))
            ->inlineLabel()
            ->default(false)
            ->live()
            ->hidden(function ($get) {
                if ($enum = static::getModel()::getTypeEnumClass()::tryFrom($get('type'))) {
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
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getParentIdFormComponent()
    {
        return Forms\Components\Hidden::make('parent_id')
            ->dehydratedWhenHidden()
            ->afterStateHydrated(function ($operation, $livewire, $state, $component) {
                if ($operation === 'create' && $livewire instanceof DocumentTypeForm) {
                    $parentKey = $livewire->getParentKey();

                    if ($livewire->canBeParent($parentKey)) {
                        $component->state($parentKey);

                        return;
                    }
                }

                $component->state($state);

            });
    }
    //endregion Form field(s)/component(s)
}
