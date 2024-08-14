<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource\Pages;

use SolutionForest\InspireCms\Filament\Resources\Pages\EditWithDetailInfoPage;
use SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource;

class EditFieldGroup extends EditWithDetailInfoPage
{
    public static function getResource(): string
    {
        return config('inspirecms-core.resources.field_group', FieldGroupResource::class);
    }

    public function wrapMainFormBySection(): bool
    {
        return false;
    }
}
