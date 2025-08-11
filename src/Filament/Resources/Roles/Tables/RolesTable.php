<?php

namespace SolutionForest\InspireCms\Filament\Resources\Roles\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Helpers\AuthHelper;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->whereGuardName(AuthHelper::guardName()))
            ->columns([
                TextColumn::make('name')
                    ->label(__('inspirecms::resources/role.name.label'))
                    ->badge(),
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
            ]);
    }
}
