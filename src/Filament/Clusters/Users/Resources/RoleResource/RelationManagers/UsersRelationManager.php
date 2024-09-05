<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Models\Contracts\User;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';
    protected static ?string $icon = 'heroicon-o-users';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label(' ')
                    ->circular()
                    ->getStateUsing(fn (User $record) => $record->getFilamentAvatarUrl() ?? filament()->getUserAvatarUrl($record)),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('inspirecms::inspirecms.name')),
            ])
            ->headerActions([
                Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
