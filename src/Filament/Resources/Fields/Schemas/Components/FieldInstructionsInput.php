<?php

namespace SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components;

use Filament\Forms\Components\TextInput;

class FieldInstructionsInput
{
    public static function make(): TextInput
    {
        return TextInput::make('instructions')
            ->label(__('inspirecms::resources/field.instructions.label'))
            ->validationAttribute(__('inspirecms::resources/field.instructions.validation_attribute'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::resources/field.instructions.label'))
            ->helperText(__('inspirecms::resources/field.instructions.instructions'))
            ->maxLength(255)
            ->columnSpan('full');
    }
}
