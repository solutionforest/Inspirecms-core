<?php

namespace SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class FieldLabelInput
{
    public static function make(): TextInput
    {
        return TextInput::make('label')
            ->label(__('inspirecms::resources/field.label.label'))
            ->validationAttribute(__('inspirecms::resources/field.label.validation_attribute'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::resources/field.label.label'))
            ->helperText(__('inspirecms::resources/field.label.instructions'))
            ->required()
            ->columnSpan('full')
            ->maxLength(255)
            ->live(debounce: 500)
            ->afterStateUpdated(fn ($set, ?string $state) => $set('name', Str::slug($state, '_')));
    }
}
