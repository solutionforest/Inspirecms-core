<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components;

use Filament\Forms\Components\FileUpload;
use SolutionForest\InspireCms\InspireCmsConfig;

class UserAvatarUpload
{
    public static function make(): FileUpload
    {
        return FileUpload::make('avatar')
            ->label(__('inspirecms::resources/user.avatar.label'))
            ->validationAttribute(__('inspirecms::resources/user.avatar.validation_attribute'))
            ->disk(InspireCmsConfig::get('media.user_avatar.driver', 'public'))
            ->directory(InspireCmsConfig::get('media.user_avatar.directory', 'avatars'))
            ->image();
    }
}
