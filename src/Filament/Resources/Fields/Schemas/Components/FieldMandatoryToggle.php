<?php

namespace SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components;

use Filament\Forms\Components\Toggle;

class FieldMandatoryToggle
{
    public static function make(): Toggle
    {
        return Toggle::make('mandatory')
            ->label(__('inspirecms::resources/field.mandatory.label'))
            ->validationAttribute(__('inspirecms::resources/field.mandatory.validation_attribute'))
            ->inlineLabel()
            ->helperText(__('inspirecms::resources/field.mandatory.instructions'))
            ->inlineLabel();
    }
}
