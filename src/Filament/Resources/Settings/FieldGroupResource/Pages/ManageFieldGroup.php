<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource\Pages;

use SolutionForest\FilamentFieldGroup\Filament\Resources\FieldGroupResource\Pages\ManageFieldGroup as BasePage;
use SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource;

class ManageFieldGroup extends BasePage
{
    public static function getResource(): string
    {
        return config('inspirecms-core.resources.field_group', FieldGroupResource::class);
    }
}
