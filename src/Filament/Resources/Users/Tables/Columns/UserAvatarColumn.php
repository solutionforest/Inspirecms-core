<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Tables\Columns;

use Filament\Tables\Columns\ImageColumn;
use SolutionForest\InspireCms\Models\Contracts\User;

class UserAvatarColumn
{
    public static function make()
    {
        return ImageColumn::make('avatar')
            ->label(' ')
            ->circular()
            ->getStateUsing(fn (User $record) => $record->getFilamentAvatarUrl() ?? filament()->getUserAvatarUrl($record));
    }
}
