<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Enums\SitemapChangeFrequency;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Resources\SitemapResource\Pages\ManageSitemap;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Sitemap;

class SitemapResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -6;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $cluster = Settings::class;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
            'update',
            'delete',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                TextColumn::make('type')
                    ->label(__('inspirecms::resources/sitemap.type.label'))
                    ->getStateUsing(fn (Model | Sitemap $record) => $record->getType()),
                TextColumn::make('url')
                    ->label(__('inspirecms::resources/sitemap.url.label'))
                    ->getStateUsing(fn (Model | Sitemap $record) => $record->getUrl()),
                TextColumn::make('priority')
                    ->label(__('inspirecms::resources/sitemap.priority.label'))
                    ->sortable(),
                TextColumn::make('change_frequency')
                    ->label(__('inspirecms::resources/sitemap.change_frequency.label'))
                    ->formatStateUsing(fn ($state) => SitemapChangeFrequency::tryFrom($state)?->getLabel()),
                CheckboxColumn::make('enable')
                    ->label(__('inspirecms::resources/sitemap.enable.label'))
                    ->disabled(fn (Model | Sitemap $record) => ! static::canEdit($record)),
                TextColumn::make('updated_at')
                    ->label(__('inspirecms::inspirecms.last_updated_at'))
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->visible(fn (Model | Sitemap $record) => static::canEdit($record))->slideOver(),
                DeleteAction::make()->iconButton()->visible(fn (Model | Sitemap $record) => static::canDelete($record)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSitemap::route('/'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getSitemapModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.sitemap.singular');
    }

    // region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    // endregion Global search

    public static function canEdit(Model $record): bool
    {
        if ($record instanceof Sitemap && $record->getType() != 'general') {
            return false;
        }

        return parent::canEdit($record);
    }

    public static function canDelete(Model $record): bool
    {
        if ($record instanceof Sitemap && $record->getType() != 'general') {
            return false;
        }

        return parent::canDelete($record);
    }

    // region Form field(s)/component(s)
    /** @return Field|Component */
    protected static function getUrlFormComponent()
    {
        return TextInput::make('url')
            ->label(__('inspirecms::resources/sitemap.url.label'))
            ->validationAttribute(__('inspirecms::resources/sitemap.url.validation_attribute'))
            ->url()
            ->columnSpanFull()
            ->required();
    }

    /** @return Field|Component */
    protected static function getPriorityFormComponent()
    {
        return TextInput::make('priority')
            ->label(__('inspirecms::resources/sitemap.priority.label'))
            ->validationAttribute(__('inspirecms::resources/sitemap.priority.validation_attribute'))
            ->numeric()
            ->inputMode('decimal')
            ->maxValue(1)
            ->minValue(0)
            ->step(0.1)
            ->afterStateHydrated(fn ($component, $state) => $component->state($state ?? 0.5))
            ->dehydrateStateUsing(fn ($state) => $state ?? 0.5)
            ->required();
    }

    /** @return Field|Component */
    protected static function getChangeFrequencyFormComponent()
    {
        return Select::make('change_frequency')
            ->label(__('inspirecms::resources/sitemap.change_frequency.label'))
            ->validationAttribute(__('inspirecms::resources/sitemap.change_frequency.validation_attribute'))
            ->options(SitemapChangeFrequency::class)
            ->afterStateHydrated(fn ($component, $state) => $component->state($state ?? SitemapChangeFrequency::Monthly->value))
            ->dehydrateStateUsing(fn ($state) => $state ?? SitemapChangeFrequency::Monthly->value)
            ->required();
    }

    /** @return Field|Component */
    protected static function getEnableFormComponent()
    {
        return Toggle::make('enable')
            ->label(__('inspirecms::resources/sitemap.enable.label'))
            ->validationAttribute(__('inspirecms::resources/sitemap.enable.validation_attribute'))
            ->default(true);
    }
    // endregion Form field(s)/component(s)
}
