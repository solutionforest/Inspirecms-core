<?php

namespace SolutionForest\InspireCms\Filament\Resources\UserResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use SolutionForest\InspireCms\Base\Filament\Pages\Concerns\ProfilePageTrait;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreateRecord;
use SolutionForest\InspireCms\Filament\Resources\UserResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class CreateUser extends BaseCreateRecord
{
    use ProfilePageTrait;

    public function getActions(): array
    {
        return [];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('user', UserResource::class);
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        static::getNameFormComponent(),
                        static::getEmailFormComponent(),
                        static::getPasswordFormComponent(),
                        static::getPasswordConfirmationFormComponent(),
                    ])
                    ->columnSpan(2),
                Forms\Components\Section::make()
                    ->schema([
                        static::getRolesFormComponent(),
                    ])
                    ->columnSpan(1),
            ]);
    }

    public function afterCreate()
    {
        $user = $this->record;

        if ($user && $user instanceof Authenticatable) {
            event(new Registered($user));
        }
    }
}
