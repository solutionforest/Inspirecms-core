<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Facades\LocaleManifest;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\LanguageResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class LanguageResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -8;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $cluster = Settings::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getCodeFormComponent(),
                static::getNameFormComponent(),
                static::getIsDefaultFormComponent(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('inspirecms::inspirecms.code'))
                    ->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('inspirecms::inspirecms.name')),
                Tables\Columns\CheckboxColumn::make('is_default')
                    ->label(__('inspirecms::inspirecms.is_default'))
                    ->width('1%')
                    ->alignCenter()->verticallyAlignCenter()
                    ->disabled(fn ($record) => ! static::canEdit($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->iconButton(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('inspirecms::inspirecms.is_default')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLanguages::route('/'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getLanguageModelClass();
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
    protected static function getCodeFormComponent()
    {
        return Forms\Components\TextInput::make('code')
            ->label(__('inspirecms::inspirecms.code'))
            ->unique(table: static::getModel(), column: 'code', ignoreRecord: true)
            ->datalist(LocaleManifest::getLocales())
            ->live()->afterStateUpdated(function (?string $state, Forms\Set $set) {
                if (filled($state)) {
                    $set('name', locale_get_display_name($state));
                }
            })
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getNameFormComponent()
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::inspirecms.name'))
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getIsDefaultFormComponent()
    {
        return Forms\Components\Toggle::make('is_default')
            ->label(__('inspirecms::inspirecms.is_default'))
            ->default(false);
    }
    //endregion Form field(s)/component(s)
}
