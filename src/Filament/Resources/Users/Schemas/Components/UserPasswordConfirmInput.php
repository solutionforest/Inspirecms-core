<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components;

use Filament\Forms\Components\TextInput;

class UserPasswordConfirmInput
{
    public static function make(): TextInput
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('inspirecms::resources/user.password_confirmation.label'))
            ->validationAttribute(__('inspirecms::resources/user.password_confirmation.validation_attribute'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false);
    }
}
