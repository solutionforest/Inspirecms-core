<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Tables\Columns;

use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;

class UserEmailColumn
{
    public static function make()
    {
        return TextColumn::make('email')
            ->label(__('inspirecms::resources/user.email.label'))
            ->copyable()
            ->icon(FilamentIcon::resolve('inspirecms::email'));
    }
}
