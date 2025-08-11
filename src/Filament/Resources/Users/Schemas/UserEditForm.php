<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserAvatarUpload;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserDetailDisplayGroup;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserEmailInput;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserNameInput;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserPasswordConfirmInput;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserPasswordInput;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserPreferredLanguageInput;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserRolePicker;

class UserEditForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                UserNameInput::make(),
                                UserEmailInput::make(),
                                UserPreferredLanguageInput::make(),
                            ]),
                        Section::make()
                            ->schema([
                                UserRolePicker::make(),
                            ]),
                    ])
                    ->columnSpan(2),
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                UserAvatarUpload::make(),
                            ]),
                        Section::make()
                            ->schema([
                                UserPasswordInput::make()
                                    ->dehydrated(fn ($state): bool => filled($state))
                                    ->live(debounce: 500),
                                UserPasswordConfirmInput::make()
                                    ->visible(fn ($get): bool => filled($get('password'))),
                            ]),
                        UserDetailDisplayGroup::make(),
                    ])
                    ->columns(1)
                    ->columnSpan(1),
            ]);
    }
}
