<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentPicker;
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
                static::getNavigationTypeFormComponent(),
                static::getTitleFormComponent(),
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
                    ->label(__('inspirecms::inspirecms.target'))
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
                Tables\Actions\EditAction::make()
                    ->iconButton(),
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
        return ContentPicker::make('content_id')
            ->label(__('inspirecms::inspirecms.content'))
            ->maxItems(1)
            ->columnSpanFull()
            ->requiredIf('type', 'content')
            ->markAsRequired()
            ->visible(fn ($get) => $get('type') == 'content')
            ->afterStateHydrated(function ($state, $component, $record) {
                if ($record->type == 'content') {
                    $component->state([$state]);
                }
            })
            ->dehydrateStateUsing(fn ($get, $state) => $get('type') == 'content' ? $state[0] ?? null : null)
            ->dehydratedWhenHidden();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getUrlFormComponent()
    {
        return Forms\Components\TextInput::make('url')
            ->label(__('inspirecms::inspirecms.url'))
            ->columnSpanFull()
            ->requiredIf('type', 'link')
            ->markAsRequired()
            ->visible(fn ($get) => $get('type') == 'link')
            ->dehydrateStateUsing(fn ($get, $state) => $get('type') == 'link' ? $state : null)
            ->dehydratedWhenHidden();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTypeFormComponent()
    {
        return Forms\Components\Select::make('type')
            ->label(__('inspirecms::inspirecms.type'))
            ->inlineLabel()
            ->columnSpanFull()
            ->live()
            ->required()
            //todo: translate
            ->options([
                'content' => __('inspirecms::inspirecms.content'),
                'link' => __('inspirecms::inspirecms.link'),
            ])
            ->default('content');
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getNavigationTypeFormComponent()
    {
        return Forms\Components\TextInput::make('navigation_type')
            ->label(__('inspirecms::inspirecms.navigation_type'))
            ->required()
            //todo: translate
            ->datalist([
                'main',
                'footer',
            ])
            ->default('main');
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
    //endregion Form field(s)/component(s)
}
