<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components;

use Filament\Forms\Components\Select;
use SolutionForest\InspireCms\Facades\LocalizationManager;

class UserPreferredLanguageInput
{
    public static function make(): Select
    {
        return Select::make('preferred_language')
            ->label(__('inspirecms::resources/user.preferred_language.label'))
            ->validationAttribute(__('inspirecms::resources/user.preferred_language.validation_attribute'))
            ->options(LocalizationManager::getLocaleLabelsFor(LocalizationManager::getUserPreferredLocales()))
            ->searchable()
            ->required();
    }
}
