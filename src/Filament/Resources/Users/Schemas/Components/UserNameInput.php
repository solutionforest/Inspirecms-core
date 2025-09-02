<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components;

use Filament\Forms\Components\TextInput;

class UserNameInput
{
    public static function make(): TextInput
    {
        return TextInput::make('name')
            ->label(__('inspirecms::resources/user.name.label'))
            ->required()
            ->maxLength(255);
    }
}
