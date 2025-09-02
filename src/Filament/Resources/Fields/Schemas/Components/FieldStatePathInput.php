<?php

namespace SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components;

use Filament\Forms\Components\TextInput;

class FieldStatePathInput
{
    public static function make(): TextInput
    {
        return TextInput::make('state_path')
            ->label(__('inspirecms::resources/field.state_path.label'))
            ->validationAttribute(__('inspirecms::resources/field.state_path.validation_attribute'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::resources/field.state_path.label'))
            ->helperText(__('inspirecms::resources/field.state_path.instructions'))
            ->maxLength(255);
    }
}
