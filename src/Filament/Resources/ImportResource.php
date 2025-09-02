<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Resources\Imports\Tables\ImportsTable;
use SolutionForest\InspireCms\InspireCmsConfig;

class ImportResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -3;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-c-arrow-up-tray';

    protected static ?string $cluster = Settings::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'create',
        ];
    }

    public static function table(Table $table): Table
    {
        return ImportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getImportModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.import');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'author',
            ])
            ->where(function (Builder $query) {

                $currentUser = auth()->user();

                return $query
                    ->when(! has_super_admin_role($currentUser), fn (Builder $q) => $q->whereMorphedTo('author', $currentUser));
            });
    }

    // region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    // endregion Global search
}
