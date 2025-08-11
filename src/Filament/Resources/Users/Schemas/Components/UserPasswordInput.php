<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserPasswordInput
{
    public static function make(): TextInput
    {
        return TextInput::make('password')
            ->label(__('inspirecms::resources/user.password.label'))
            ->validationAttribute(__('inspirecms::resources/user.password.validation_attribute'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->rule(Password::default())
            ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
            ->same('passwordConfirmation');
    }
}
