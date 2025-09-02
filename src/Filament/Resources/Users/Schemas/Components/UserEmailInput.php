<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components;

use Filament\Forms\Components\TextInput;

class UserEmailInput
{
    public static function make(): TextInput
    {
        return TextInput::make('email')
            ->label(__('inspirecms::resources/user.email.label'))
            ->validationAttribute(__('inspirecms::resources/user.email.validation_attribute'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true);
    }
}
