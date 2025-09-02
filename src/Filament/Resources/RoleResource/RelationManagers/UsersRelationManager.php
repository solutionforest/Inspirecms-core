<?php

namespace SolutionForest\InspireCms\Filament\Resources\RoleResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Resources\Users\Tables\UsersAssociationTable;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | \BackedEnum | null $icon = 'heroicon-o-users';

    public function table(Table $table): Table
    {
        return UsersAssociationTable::configure($table);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::inspirecms.user.plural');
    }
}
