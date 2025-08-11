<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserEmailInput;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserNameInput;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserPasswordConfirmInput;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserPasswordInput;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserRolePicker;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make()
                    ->schema([
                        UserNameInput::make(),
                        UserEmailInput::make(),
                        UserPasswordInput::make()
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->live(debounce: 500),
                        UserPasswordConfirmInput::make(),
                    ])
                    ->columnSpan(2),
                Section::make()
                    ->schema([
                        UserRolePicker::make(),
                    ])
                    ->columnSpan(1),
            ]);
    }
}
