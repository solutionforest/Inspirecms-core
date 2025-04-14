<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Facades\LocalizationManager;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Resources\LanguageResource\Pages;
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
                    ->label(__('inspirecms::resources/language.code.label'))
                    ->sortable()
                    ->width('5%'),
                Tables\Columns\TextColumn::make('display_name')
                    ->label(__('inspirecms::resources/language.display_name.label'))
                    ->getStateUsing(fn ($record) => filled($record->code) ? LocalizationManager::getLocaleLabel($record->code) : null)
                    ->extraAttributes(fn (Model | Language $record) => [
                        'data-locale' => $record->code,
                    ]),
                Tables\Columns\CheckboxColumn::make('is_default')
                    ->label(__('inspirecms::resources/language.is_default.label'))
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
                    ->label(__('inspirecms::resources/language.is_default.label')),
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
    protected static function getCodeFormComponent()
    {
        return Forms\Components\TextInput::make('code')
            ->label(__('inspirecms::resources/language.code.label'))
            ->validationAttribute(__('inspirecms::resources/language.code.validation_attribute'))
            ->unique(table: static::getModel(), column: 'code', ignoreRecord: true)
            ->datalist(LocalizationManager::getLocales())
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getIsDefaultFormComponent()
    {
        return Forms\Components\Toggle::make('is_default')
            ->label(__('inspirecms::resources/language.is_default.label'))
            ->validationAttribute(__('inspirecms::resources/language.is_default.validation_attribute'))
            ->default(false);
    }
    // endregion Form field(s)/component(s)
}
