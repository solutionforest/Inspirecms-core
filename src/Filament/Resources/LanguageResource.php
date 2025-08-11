<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Facades\LocalizationManager;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Resources\LanguageResource\Pages\ListLanguages;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Language;

class LanguageResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -8;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-language';

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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                static::getCodeFormComponent(),
                static::getIsDefaultFormComponent(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('inspirecms::resources/language.code.label'))
                    ->sortable()
                    ->width('5%'),
                TextColumn::make('display_name')
                    ->label(__('inspirecms::resources/language.display_name.label'))
                    ->getStateUsing(fn ($record) => filled($record->code) ? LocalizationManager::getLocaleLabel($record->code) : null)
                    ->extraAttributes(fn (Model | Language $record) => [
                        'data-locale' => $record->code,
                    ]),
                CheckboxColumn::make('is_default')
                    ->label(__('inspirecms::resources/language.is_default.label'))
                    ->width('1%')
                    ->alignCenter()->verticallyAlignCenter()
                    ->disabled(fn (Model | Language $record) => ! static::canEdit($record)),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->iconButton(),
            ])
            ->filters([
                TernaryFilter::make('is_default')
                    ->label(__('inspirecms::resources/language.is_default.label')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLanguages::route('/'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getLanguageModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.language.singular');
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
    protected static function getCodeFormComponent()
    {
        return TextInput::make('code')
            ->label(__('inspirecms::resources/language.code.label'))
            ->validationAttribute(__('inspirecms::resources/language.code.validation_attribute'))
            ->unique(table: static::getModel(), column: 'code', ignoreRecord: true)
            ->datalist(LocalizationManager::getLocales())
            ->required();
    }

    /**
     * @return Field|Component
     */
    protected static function getIsDefaultFormComponent()
    {
        return Toggle::make('is_default')
            ->label(__('inspirecms::resources/language.is_default.label'))
            ->validationAttribute(__('inspirecms::resources/language.is_default.validation_attribute'))
            ->default(false);
    }
    // endregion Form field(s)/component(s)
}
