<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Facades\LocaleManifest;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\LanguageResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Language;

class LanguageResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -8;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $cluster = Settings::class;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                static::getCodeFormComponent(),
                static::getIsDefaultFormComponent(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('inspirecms::inspirecms.code'))
                    ->sortable(),
                Tables\Columns\CheckboxColumn::make('is_default')
                    ->label(__('inspirecms::inspirecms.is_default'))
                    ->width('1%')
                    ->alignCenter()->verticallyAlignCenter()
                    ->disabled(fn (Model | Language $record) => ! static::canEdit($record)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
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

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.language');
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
