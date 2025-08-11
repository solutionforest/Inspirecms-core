<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Filament\Clusters\Users;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Resources\RoleResource\Pages\CreateRole;
use SolutionForest\InspireCms\Filament\Resources\RoleResource\Pages\EditRole;
use SolutionForest\InspireCms\Filament\Resources\RoleResource\Pages\ListRoles;
use SolutionForest\InspireCms\Filament\Resources\RoleResource\Pages\ViewRole;
use SolutionForest\InspireCms\Filament\Resources\RoleResource\RelationManagers\UsersRelationManager;
use SolutionForest\InspireCms\Filament\Resources\Roles\Schemas\RoleForm;
use SolutionForest\InspireCms\Filament\Resources\Roles\Tables\Columns\AllowClustersColumn;
use SolutionForest\InspireCms\Filament\Resources\Roles\Tables\RolesTable;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Licensing\LicenseManager;

class RoleResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $cluster = Users::class;

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table)
            ->pushColumns([
                AllowClustersColumn::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'users' => UsersRelationManager::class,
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getRoleModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.role.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('inspirecms::inspirecms.role.plural');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('permissions')
            ->where('guard_name', AuthHelper::guardName());
    }

    // region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }

    // endregion Global search

    public static function canCreate(): bool
    {
        if (! app(LicenseManager::class)->canCreateRole()) {
            return false;
        }

        return parent::canCreate();
    }
}
