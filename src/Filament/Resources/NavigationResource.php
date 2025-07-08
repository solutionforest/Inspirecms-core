<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\InspireCms\Base\Enums\NavigationType;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentPicker;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\Filter as ContentPickerFilters;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource\Pages;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Navigation;

class NavigationResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;
    use Translatable;

    protected static ?int $navigationSort = -7;

    protected static ?string $navigationIcon = 'heroicon-o-bars-4';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $slug = 'navigation';

    protected static ?string $cluster = Settings::class;

    public static function getTranslatableLocales(): array
    {
        return array_keys(InspireCms::getAllAvailableLanguages());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        static::getCategoryFormComponent(),
                        static::getParentFormComponent(),
                        static::getIsActiveFormComponent(),
                    ]),
                Forms\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        static::getTitleFormComponent()->columnSpanFull(),
                        static::getTypeFormComponent(),
                        static::getContentFormComponent(),
                        static::getUrlFormComponent(),
                        static::getTargetFormComponent(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Tables\Grouping\Group::make('category')
                    ->label(__('inspirecms::resources/navigation.category.label')),
                Tables\Grouping\Group::make('type')
                    ->label(__('inspirecms::resources/navigation.type.label'))
                    ->getTitleFromRecordUsing(fn (Model | Navigation $record) => $record->display_type?->getLabel()),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with(['parent']))
            ->defaultSort('created_at', 'desc')
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id')),

                Tables\Columns\TextColumn::make('category')
                    ->label(__('inspirecms::resources/navigation.category.label'))
                    ->badge()
                    ->width('5%'),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::resources/navigation.title.label')),

                Tables\Columns\ColumnGroup::make(__('inspirecms::inspirecms.url'), [
                    Tables\Columns\TextColumn::make('display_type')
                        ->label(__('inspirecms::resources/navigation.type.label'))
                        ->badge()
                        ->width('5%'),
                    Tables\Columns\TextColumn::make('url')
                        ->label(fn () => '')
                        ->getStateUsing(fn (Model | Navigation $record, $livewire) => $record->getUrl($livewire->getActiveActionsLocale() ?? app()->getLocale())),
                ])->alignCenter(),

                Tables\Columns\TextColumn::make('target')
                    ->label(__('inspirecms::resources/navigation.target.label')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()->slideOver(),
                Tables\Actions\ViewAction::make()->iconButton()->slideOver(),
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
            'index' => Pages\ListNavigationTree::route('/tree'),
            'table' => Pages\ListNavigationTable::route('/table'),
            'create' => Pages\CreateNavigation::route('/create'),
            'edit' => Pages\EditNavigation::route('/{record}/edit'),
            'view' => Pages\ViewNavigation::route('/{record}'),
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
            throw new \InvalidArgumentException('The model must implement the ' . Navigation::class . ' interface.');
        }

        return $model;
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.navigation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('inspirecms::inspirecms.navigation');
    }

    // region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    // endregion Global search

    // region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Component
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
            ->where(new ContentPickerFilters\WithoutGlobalScope(SoftDeletingScope::class));
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getUrlFormComponent()
    {
        return Forms\Components\TextInput::make('url')
            ->label(__('inspirecms::resources/navigation.url.label'))
            ->validationAttribute(__('inspirecms::resources/navigation.url.validation_attribute'))
            ->columnSpanFull();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTypeFormComponent()
    {
        $enumClass = static::getModel()::getNavigationTypeEnumClass();

        return Forms\Components\ToggleButtons::make('type')
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
            ->inline()->grouped();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getCategoryFormComponent()
    {
        return Forms\Components\TextInput::make('category')
            ->label(__('inspirecms::resources/navigation.category.label'))
            ->validationAttribute(__('inspirecms::resources/navigation.category.validation_attribute'))
            ->required()
            ->datalist([
                'main',
                'footer',
            ])
            ->default('main')
            ->suffixActions([
                Forms\Components\Actions\Action::make('fillFromExist')
                    ->icon('heroicon-o-pencil')
                    ->fillForm(fn ($state) => [
                        'category' => $state,
                    ])
                    ->form(function () {
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
                            Forms\Components\Select::make('category')
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
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getParentFormComponent()
    {
        return Forms\Components\Select::make('parent_id')
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
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTargetFormComponent()
    {
        return Forms\Components\TextInput::make('target')
            ->label(__('inspirecms::resources/navigation.target.label'))
            ->validationAttribute(__('inspirecms::resources/navigation.target.validation_attribute'))
            ->inlineLabel()
            ->datalist([
                '_self',
                '_blank',
            ]);
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTitleFormComponent()
    {
        return Forms\Components\TextInput::make('title')
            ->label(__('inspirecms::resources/navigation.title.label'))
            ->validationAttribute(__('inspirecms::resources/navigation.title.validation_attribute'))
            ->inlineLabel()
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getIsActiveFormComponent()
    {
        return Forms\Components\Toggle::make('is_active')
            ->label(__('inspirecms::resources/navigation.is_active.label'))
            ->validationAttribute(__('inspirecms::resources/navigation.is_active.validation_attribute'))
            ->inlineLabel()
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
