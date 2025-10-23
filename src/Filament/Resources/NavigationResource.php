<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use InvalidArgumentException;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use SolutionForest\InspireCms\Base\Enums\NavigationType;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentPicker;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\Filter as ContentPickerFilters;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource\Pages\CreateNavigation;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource\Pages\EditNavigation;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource\Pages\ListNavigationTable;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource\Pages\ListNavigationTree;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource\Pages\ViewNavigation;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Navigation;

class NavigationResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;
    use Translatable;

    protected static ?int $navigationSort = -7;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bars-4';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $slug = 'navigation';

    protected static ?string $cluster = Settings::class;

    public static function getTranslatableLocales(): array
    {
        return array_keys(InspireCms::getAllAvailableLanguages());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        static::getCategoryFormComponent(),
                        static::getParentFormComponent(),
                        static::getIsActiveFormComponent()->inlineLabel(),
                    ]),
                Section::make()
                    ->columns(2)
                    ->schema([
                        static::getTitleFormComponent()->columnSpanFull(),
                        static::getTypeFormComponent(),
                        static::getContentFormComponent(),
                        static::getUrlFormComponent()->columnSpanFull(),
                        static::getTargetFormComponent()->inlineLabel(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('category')
                    ->label(__('inspirecms::resources/navigation.category.label')),
                Group::make('type')
                    ->label(__('inspirecms::resources/navigation.type.label'))
                    ->getTitleFromRecordUsing(fn (Model | Navigation $record) => $record->display_type?->getLabel()),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with(['parent']))
            ->defaultSort('created_at', 'desc')
            ->columns([

                TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id')),

                TextColumn::make('category')
                    ->label(__('inspirecms::resources/navigation.category.label'))
                    ->badge()
                    ->width('5%'),

                TextColumn::make('title')
                    ->label(__('inspirecms::resources/navigation.title.label')),

                ColumnGroup::make(__('inspirecms::inspirecms.url'), [
                    TextColumn::make('display_type')
                        ->label(__('inspirecms::resources/navigation.type.label'))
                        ->badge()
                        ->width('5%'),
                    TextColumn::make('url')
                        ->label(fn () => '')
                        ->getStateUsing(fn (Model | Navigation $record, $livewire) => $record->getUrl($livewire->getActiveActionsLocale() ?? app()->getLocale())),
                ])->alignCenter(),

                TextColumn::make('target')
                    ->label(__('inspirecms::resources/navigation.target.label')),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->slideOver(),
                ViewAction::make()->iconButton()->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNavigationTree::route('/tree'),
            'table' => ListNavigationTable::route('/table'),
            'create' => CreateNavigation::route('/create'),
            'edit' => EditNavigation::route('/{record}/edit'),
            'view' => ViewNavigation::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'content' => fn ($q) => $q->withTrashed(),
            'children',
        ]);
    }

    /**
     * @return class-string<Navigation>
     */
    public static function getModel(): string
    {
        return static::guardAgainstInvalidModel(InspireCmsConfig::getNavigationModelClass());
    }

    protected static function guardAgainstInvalidModel(string $model): string
    {
        if (! in_array(Navigation::class, class_implements($model))) {
            throw new InvalidArgumentException('The model must implement the ' . Navigation::class . ' interface.');
        }

        return $model;
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.navigation.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('inspirecms::inspirecms.navigation.plural');
    }

    // region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }

    // endregion Global search
    // region Form field(s)/component(s)
    /**
     * @return Field|Component
     */
    protected static function getContentFormComponent()
    {
        $requiredOrDisplayIfContent = function ($type) {
            return $type == NavigationType::Content ||
                $type == NavigationType::Content->value;
        };

        return ContentPicker::make('content_id')
            ->label(__('inspirecms::resources/navigation.content.label'))
            ->validationAttribute(__('inspirecms::resources/navigation.content.validation_attribute'))
            ->maxItems(1)
            ->minItems(function ($get) use ($requiredOrDisplayIfContent) {
                return $requiredOrDisplayIfContent($get('type')) ? 1 : 0;
            })
            ->columnSpanFull()
            ->required(fn ($get) => $requiredOrDisplayIfContent($get('type')))
            ->markAsRequired()
            ->visible(fn ($get) => $requiredOrDisplayIfContent($get('type')))
            ->afterStateHydrated(function ($state, $component, $record) {
                if (empty($state) ||
                    is_null($state) ||
                    (is_string($state) && $state == app(static::getModel())::defaultContentId())
                ) {
                    $state = [];
                } else {
                    $state = [$state];
                }
                $component->state($state);
            })
            ->dehydrateStateUsing(function ($get, $state) use ($requiredOrDisplayIfContent) {
                return $requiredOrDisplayIfContent($get('type')) ?
                    $state[0] ?? null :
                    null;
            })
            // display deleted content
            ->where(new ContentPickerFilters\WithoutGlobalScope(SoftDeletingScope::class))
            // only display web page content
            ->where(new ContentPickerFilters\BuilderFilter('whereIsWebPage'));
    }

    /**
     * @return Field|Component
     */
    protected static function getUrlFormComponent()
    {
        return TextInput::make('url')
            ->label(__('inspirecms::resources/navigation.url.label'))
            ->validationAttribute(__('inspirecms::resources/navigation.url.validation_attribute'))
            ->visible(function ($get) {
                return $get('type') == NavigationType::Link ||
                    $get('type') == NavigationType::Link->value;
            });
    }

    /**
     * @return Field|Component
     */
    protected static function getTypeFormComponent()
    {
        $enumClass = static::getModel()::getNavigationTypeEnumClass();

        return ToggleButtons::make('type')
            ->label(__('inspirecms::resources/navigation.type.label'))
            ->validationAttribute(__('inspirecms::resources/navigation.type.validation_attribute'))
            ->columnSpanFull()
            ->live()
            ->required()
            ->options($enumClass)
            ->default($enumClass::getDefaultValue())
            ->afterStateUpdated(function ($state, $set, $operation) {
                if ($operation == 'create' &&
                    ($state == NavigationType::Content->value || $state == NavigationType::Content)) {
                    $set('is_active', true);
                }
            })
            // avoid dirty state issue for model
            ->dehydrateStateUsing(fn ($state) => $state instanceof \BackedEnum ? $state->value : $state)
            ->inline()->grouped();
    }

    /**
     * @return Field|Component
     */
    protected static function getCategoryFormComponent()
    {
        return TextInput::make('category')
            ->label(__('inspirecms::resources/navigation.category.label'))
            ->validationAttribute(__('inspirecms::resources/navigation.category.validation_attribute'))
            ->required()
            ->datalist([
                'main',
                'footer',
            ])
            ->default('main')
            ->suffixActions([
                Action::make('fillFromExist')
                    ->icon('heroicon-o-pencil')
                    ->fillForm(fn ($state) => [
                        'category' => $state,
                    ])
                    ->schema(function () {
                        $getCategoryOptions = function ($search = null, $limit = 50) {
                            $query = static::getEloquentQuery()
                                ->select('category')
                                ->distinct()
                                ->when($search, function ($query, $search) {
                                    $query->where('category', 'like', '%' . $search . '%');
                                })
                                ->limit($limit);

                            return $query->pluck('category', 'category')->all();
                        };

                        return [
                            Select::make('category')
                                ->label(__('inspirecms::resources/navigation.category.label'))
                                ->validationAttribute(__('inspirecms::resources/navigation.category.validation_attribute'))
                                ->options(fn () => $getCategoryOptions())
                                ->searchable()
                                ->getSearchResultsUsing(fn ($search) => $getCategoryOptions($search))
                                ->default(fn ($record, $get) => $get('category'))
                                ->required(),
                        ];
                    })
                    ->action(function ($data, $set) {
                        $set('category', $data['category']);
                    }),
            ])
            ->live()
            ->afterStateUpdated(function ($old, $state, $set) {
                if (trim($old) !== trim($state)) {
                    $set('parent_id', null);
                }
            });
    }

    /**
     * @return Field|Component
     */
    protected static function getParentFormComponent()
    {
        return Select::make('parent_id')
            ->label(__('inspirecms::resources/navigation.parent_id.label'))
            ->validationAttribute(__('inspirecms::resources/navigation.parent_id.validation_attribute'))
            ->options(function ($record, $get) {
                $keyName = app(static::getModel())->getKeyName();

                $traverse = function ($categories, $prefix = '-') use (&$traverse, $keyName) {
                    return collect($categories)->map(function ($category) use ($traverse, $prefix, $keyName) {
                        $label = $prefix . ' ' . $category->title;

                        $key = $category->{$keyName};

                        $children = $traverse($category->children, $prefix . '-');

                        return [
                            'label' => $label,
                            'value' => $key,
                            'children' => $children,
                        ];
                    })->all();
                };

                $records = static::getEloquentQuery()
                    ->where('category', $get('category'))
                    ->when($record, fn ($query) => $query->whereNot($keyName, $record->getKey()))
                    ->withDepth()
                    ->defaultOrder()
                    ->get()
                    ->toTree();

                $tmpOpts = collect($traverse($records))
                    ->flatten()
                    // even = labels, odd = values
                    ->reduce(function ($carry, $item, $index) {
                        $carry ??= [];
                        // array<0> = labels, array<1> = values
                        if ($index % 2 === 0) {
                            $carry[0][] = $item;
                        } else {
                            $carry[1][] = $item;
                        }

                        return $carry;
                    }, []);

                return array_combine($tmpOpts[1] ?? [], $tmpOpts[0] ?? []);
            })
            ->saveRelationshipsUsing(function ($record, $state) {
                $record->setParentId($state);
                $record->saveQuietly();
            });
    }

    /**
     * @return Field|Component
     */
    protected static function getTargetFormComponent()
    {
        return TextInput::make('target')
            ->label(__('inspirecms::resources/navigation.target.label'))
            ->validationAttribute(__('inspirecms::resources/navigation.target.validation_attribute'))
            ->datalist([
                '_self',
                '_blank',
            ]);
    }

    /**
     * @return Field|Component
     */
    protected static function getTitleFormComponent()
    {
        return TextInput::make('title')
            ->label(__('inspirecms::resources/navigation.title.label'))
            ->validationAttribute(__('inspirecms::resources/navigation.title.validation_attribute'))
            ->required();
    }

    /**
     * @return Field|Component
     */
    protected static function getIsActiveFormComponent()
    {
        return Toggle::make('is_active')
            ->label(__('inspirecms::resources/navigation.is_active.label'))
            ->validationAttribute(__('inspirecms::resources/navigation.is_active.validation_attribute'))
            ->default(true)
            ->disabled(function ($get, null | Model | Navigation $record, $operation) {
                $type = $operation == 'create' ? $get('type') : $record?->type;
                if (! $type instanceof \SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationType) {
                    $enumClass = static::getModel()::getNavigationTypeEnumClass();
                    $type = $enumClass::tryFrom($type);
                }
                if ($type) {
                    return ! $type->canEditIsVisible();
                }

                return false;
            })
            ->dehydrated(true);
    }
    // endregion Form field(s)/component(s)
}
