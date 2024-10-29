<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Base\Enums\NavigationType;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Pages;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Widgets;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentPicker;
use SolutionForest\InspireCms\Models\Contracts\Navigation;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class NavigationResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;
    use Translatable;

    protected static ?int $navigationSort = -7;

    protected static ?string $navigationIcon = 'heroicon-o-bars-4';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Settings::class;

    public static function getTranslatableLocales(): array
    {
        return array_keys(InspireCms::getAllAvailableLanguages());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getCategoryFormComponent(),
                Forms\Components\Grid::make(2)
                    ->schema([
                        static::getTitleFormComponent(),
                        static::getIsActiveFormComponent(),
                    ]),
                static::getTypeFormComponent(),
                static::getContentFormComponent(),
                static::getUrlFormComponent(),
                static::getTargetFormComponent(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Tables\Grouping\Group::make('category')
                    ->label(__('inspirecms::inspirecms.category'))
                    ->getTitleFromRecordUsing(fn ($record) => $record->getNavigationCategoryEnum()?->getLabel()),
                Tables\Grouping\Group::make('type')
                    ->label(__('inspirecms::inspirecms.type'))
                    ->getTitleFromRecordUsing(fn ($record) => $record->getNavigationTypeEnum()?->getLabel()),
            ])
            ->defaultGroup('category')
            ->modifyQueryUsing(fn ($query) => $query->with(['parent']))
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id')),

                Tables\Columns\TextColumn::make('category')
                    ->label(__('inspirecms::inspirecms.category'))
                    ->badge()
                    ->color(fn ($record) => $record->getNavigationCategoryEnum()?->getColor())
                    ->getStateUsing(fn ($record) => $record->getNavigationCategoryEnum()?->getLabel())
                    ->width('5%'),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title')),

                Tables\Columns\TextColumn::make('parent.title')
                    ->label(__('inspirecms::inspirecms.parent'))
                    ->tooltip(fn ($record) => ($parentId = $record->parent?->getKey()) ? __('inspirecms::inspirecms.id') . ': ' . $parentId : null),

                Tables\Columns\ColumnGroup::make(__('inspirecms::inspirecms.url'), [
                    Tables\Columns\TextColumn::make('type')
                        ->label(__('inspirecms::inspirecms.type'))
                        ->badge()
                        ->getStateUsing(fn ($record) => $record->getNavigationTypeEnum()?->getLabel())
                        ->width('5%'),
                    Tables\Columns\TextColumn::make('url')
                        ->label(fn () => '')
                        ->getStateUsing(fn ($record, $livewire) => $record->getUrl($livewire->getActiveActionsLocale() ?? app()->getLocale())),
                ])->alignCenter(),

                Tables\Columns\TextColumn::make('target')
                    ->label(__('inspirecms::inspirecms.target')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\ViewAction::make()->iconButton(),
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
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'content' => fn ($q) => $q->withTrashed(),
            'children',
        ]);
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getNavigationModelClass();
    }

    public static function guardAgainstInvalidModel(string $model): string
    {
        if (! in_array(Navigation::class, class_implements($model))) {
            throw new \InvalidArgumentException('The model must implement the ' . Navigation::class . ' interface.');
        }

        return $model;
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\MainNavigation::class,
            Widgets\FooterNavigation::class,
        ];
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.navigation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('inspirecms::inspirecms.navigation');
    }

    //region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    //endregion Global search

    //region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getContentFormComponent()
    {
        $requiredOrDisplayIfContent = function ($type) {
            return $type == NavigationType::Content ||
                $type == NavigationType::Content->value;
        };

        $isRelatedRecordDeleted = function ($record) {
            if (! $record) {
                return false;
            }

            if ($record->type != NavigationType::Content->value) {
                return false;
            }

            return $record->content?->trashed();
        };

        return ContentPicker::make('content_id')
            ->label(__('inspirecms::inspirecms.content'))
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
            ->modifyPaginationOptionsUsing(fn ($query, $record) => $isRelatedRecordDeleted($record) ? $query->withTrashed() : $query)
            // disable if content is deleted
            ->disabled(fn ($record) => $isRelatedRecordDeleted($record));
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getUrlFormComponent()
    {
        return Forms\Components\TextInput::make('url')
            ->label(__('inspirecms::inspirecms.url'))
            ->columnSpanFull();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTypeFormComponent()
    {
        return Forms\Components\Select::make('type')
            ->label(__('inspirecms::inspirecms.type'))
            ->columnSpanFull()
            ->live()
            ->required()
            ->options(function () {
                $model = static::guardAgainstInvalidModel(static::getModel());

                return $model::getNavigationTypeEnumClass();
            })
            ->default(function () {
                $model = static::guardAgainstInvalidModel(static::getModel());

                return $model::getNavigationTypeEnumClass()::getDefaultValue();
            })
            ->afterStateUpdated(function ($state, $set, $operation) {
                if ($operation == 'create' &&
                    ($state == NavigationType::Content->value || $state == NavigationType::Content)) {
                    $set('is_active', true);
                }
            });
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getCategoryFormComponent()
    {
        return Forms\Components\Select::make('category')
            ->label(__('inspirecms::inspirecms.category'))
            ->required()
            ->options(function () {
                $model = static::guardAgainstInvalidModel(static::getModel());

                return $model::getNavigationCategoryEnumClass();
            })
            ->default(function () {
                $model = static::guardAgainstInvalidModel(static::getModel());

                return $model::getNavigationCategoryEnumClass()::getDefaultValue();
            })
            ->live();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTargetFormComponent()
    {
        return Forms\Components\TextInput::make('target')
            ->label(__('inspirecms::inspirecms.target'))
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
            ->label(__('inspirecms::inspirecms.title'))
            ->inlineLabel()
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getIsActiveFormComponent()
    {
        return Forms\Components\Toggle::make('is_active')
            ->label(__('inspirecms::inspirecms.is_active'))
            ->inlineLabel()
            ->default(true)
            ->disabled(function ($get, $record, $operation) {
                $type = $operation == 'create' ? $get('type') : $record?->type;
                if (! $type instanceof NavigationType) {
                    $type = NavigationType::tryFrom($type);
                }
                if ($type) {
                    return ! $type->canEditIsVisible();
                }

                return false;
            })
            ->dehydrated(true);
    }
    //endregion Form field(s)/component(s)
}
