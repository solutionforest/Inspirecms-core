<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Enums\SitemapChangeFrequency;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\SitemapResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class SitemapResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -6;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $cluster = Settings::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getEnableFormComponent(),
                static::getUrlFormComponent(),
                static::getPriorityFormComponent(),
                static::getChangeFrequencyFormComponent(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('model', fn ($query) => $query->withTrashed()))
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label(__('inspirecms::resources/sitemap.type.label'))
                    ->getStateUsing(fn ($record) => $record->getType()),
                Tables\Columns\TextColumn::make('url')
                    ->label(__('inspirecms::resources/sitemap.url.label'))
                    ->getStateUsing(fn ($record) => $record->getUrl()),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('inspirecms::resources/sitemap.priority.label'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('change_frequency')
                    ->label(__('inspirecms::resources/sitemap.change_frequency.label'))
                    ->formatStateUsing(fn ($state) => SitemapChangeFrequency::tryFrom($state)?->getLabel()),
                Tables\Columns\IconColumn::make('enable')
                    ->label(__('inspirecms::resources/sitemap.enable.label'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('inspirecms::resources/sitemap.last_modified.label'))
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()->visible(fn ($record) => static::canEdit($record)),
                Tables\Actions\DeleteAction::make()->iconButton()->visible(fn ($record) => static::canDelete($record)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSitemap::route('/'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getSiteMapModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.sitemap');
    }

    //region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    //endregion Global search

    public static function canEdit(Model $record): bool
    {
        if ($record->getType() != 'general') {
            return false;
        }

        return parent::canEdit($record);
    }

    public static function canDelete(Model $record): bool
    {
        if ($record->getType() != 'general') {
            return false;
        }

        return parent::canDelete($record);
    }
    
    //region Form field(s)/component(s)
    /** @return Forms\Components\Field | Forms\Components\Component */
    protected static function getUrlFormComponent()
    {
        return Forms\Components\TextInput::make('url')
            ->label(__('inspirecms::resources/sitemap.url.label'))
            ->url()
            ->columnSpanFull()
            ->required();
    }

    /** @return Forms\Components\Field | Forms\Components\Component */
    protected static function getPriorityFormComponent()
    {
        return Forms\Components\TextInput::make('priority')
            ->label(__('inspirecms::resources/sitemap.priority.label'))
            ->numeric()
            ->inputMode('decimal')
            ->maxValue(1)
            ->minValue(0)
            ->step(0.1)
            ->afterStateHydrated(fn ($component, $state) => $component->state($state ?? 0.5))
            ->dehydrateStateUsing(fn ($state) => $state ?? 0.5)
            ->required();
    }

    /** @return Forms\Components\Field | Forms\Components\Component */
    protected static function getChangeFrequencyFormComponent()
    {
        return Forms\Components\Select::make('change_frequency')
            ->label(__('inspirecms::resources/sitemap.change_frequency.label'))
            ->options(SitemapChangeFrequency::class)
            ->afterStateHydrated(fn ($component, $state) => $component->state($state ?? SitemapChangeFrequency::Monthly->value))
            ->dehydrateStateUsing(fn ($state) => $state ?? SitemapChangeFrequency::Monthly->value)
            ->required();
    }

    /** @return Forms\Components\Field | Forms\Components\Component */
     protected static function getEnableFormComponent()
    {
        return Forms\Components\Toggle::make('enable')
            ->label(__('inspirecms::resources/sitemap.enable.label'))
            ->default(true);
    }
    //endregion Form field(s)/component(s)
}
