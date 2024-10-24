<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationCategory;
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
                static::getTitleFormComponent(),
                static::getParentFormComponent(),
                static::getTypeFormComponent(),
                static::getContentFormComponent(),
                static::getUrlFormComponent(),
                static::getTargetFormComponent(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('navigation_type')
                    ->label(__('inspirecms::inspirecms.navigation_type'))
                    ->width('5%'),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('inspirecms::inspirecms.type'))
                    ->width('5%'),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title')),
                Tables\Columns\TextColumn::make('url')
                    ->label(__('inspirecms::inspirecms.url'))
                    ->getStateUsing(fn ($record) => $record->getUrl()),
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
            'index' => Pages\ListNavigation::route('/'),
        ];
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
            ->afterStateHydrated(function ($state, $component)  use ($requiredOrDisplayIfContent) {
                if (empty($state) || is_null($state)) {
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
            });
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
            ->disabledOn(['edit'])
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
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getParentFormComponent()
    {
        $model = static::guardAgainstInvalidModel(static::getModel());
        $rootParentId = (new $model)->getNestableRootValue();

        return Forms\Components\Select::make('parent_id')
            ->label(__('inspirecms::inspirecms.parent'))
            ->options(function ($get, $record) use ($model) {
                $model = static::guardAgainstInvalidModel(static::getModel());
                $category = $get('category');
                if ($category instanceof NavigationCategory) {
                    $category = $category->value;
                }

                return $model::query()
                    ->category($category)
                    ->when($record, fn ($query) => $query->whereKeyNot($record->getKey()))
                    ->get()
                    ->mapWithKeys(fn ($record) => [$record->getKey() => $record->title]);
            })
            ->placeholder('(' . strtolower(__('inspirecms::inspirecms.no_parent') . ')'))
            ->native(false)
            // handle min uuid
            ->afterStateHydrated(function ($component, $state) use ($rootParentId) {
                if (filled($state)) {
                    if ($state == 0 || $state == $rootParentId) {
                        // If no parent ID == root level
                        $component->state(null);
                    }
                }
            })
            ->dehydrateStateUsing(function ($component, $state) use ($rootParentId) {
                if (empty($state)) {
                    return $rootParentId;
                }

                return $state;
            });
    }
    //endregion Form field(s)/component(s)
}
