<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class UserNameColumn
{
    public static function make()
    {
        return TextColumn::make('name')
            ->label(__('inspirecms::resources/user.name.label'))
            ->sortable();
    }
}
