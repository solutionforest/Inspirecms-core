<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Resources\Exports\Tables\ExportsTable;
use SolutionForest\InspireCms\InspireCmsConfig;

class ExportResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -2;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-c-arrow-down-tray';

    protected static ?string $cluster = Settings::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
        ];
    }

    public static function table(Table $table): Table
    {
        return ExportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getExportModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.export');
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
