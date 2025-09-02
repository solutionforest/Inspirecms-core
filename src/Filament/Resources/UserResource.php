<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Users;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Resources\UserResource\Pages\CreateUser;
use SolutionForest\InspireCms\Filament\Resources\UserResource\Pages\EditUser;
use SolutionForest\InspireCms\Filament\Resources\UserResource\Pages\ListUsers;
use SolutionForest\InspireCms\Filament\Resources\UserResource\Pages\ViewUser;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\UserEditForm;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\UserForm;
use SolutionForest\InspireCms\Filament\Resources\Users\Tables\UsersTable;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Licensing\LicenseManager;

class UserResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait {
        ClusterSectionResourceTrait::getPermissionPrefixes as protected traitGetPermissionPrefixes;
    }

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $cluster = Users::class;

    public static function getPermissionPrefixes(): array
    {
        return array_unique(array_merge(static::traitGetPermissionPrefixes(), ['adjust_roles']));
    }

    public static function form(Schema $schema): Schema
    {
        if ($schema->getOperation() != 'create') {
            return UserEditForm::configure($schema);
        }

        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
            'view' => ViewUser::route('{record}'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getUserModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.user.singular');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['roles']);
    }

    // region Global search
    public static function getGloballySearchableAttributes(): array
    {
        return ['id', 'email'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return UIHelper::generateTextWithDescription($record->name, $record->email);
    }
    // endregion Global search

    public static function canCreate(): bool
    {
        if (! app(LicenseManager::class)->canCreateUser()) {
            return false;
        }

        return parent::canCreate();
    }
}
