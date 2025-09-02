<?php

namespace SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\Components;

use Filament\Forms\Components\TextInput;

class FieldGroupTitleInput
{
    public static function make(): TextInput
    {
        return TextInput::make('title')
            ->label(__('inspirecms::resources/field-group.title.label'))
            ->validationAttribute(__('inspirecms::resources/field-group.name.label'))
            ->required()
            ->maxLength(255);
    }
}
