<?php

namespace SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\InspireCmsConfig;

class FieldGroupNameInput
{
    public static function make(): TextInput
    {
        return TextInput::make('name')
            ->label(__('inspirecms::resources/field-group.name.label'))
            ->validationAttribute(__('inspirecms::resources/field-group.name.validation_attribute'))
            ->required()
            ->maxLength(255)
            ->live(onBlur: true)
            ->autofocus()
            ->afterStateUpdated(function ($operation, $get, $set, $component, ?string $state) {

                $component->state(Str::slug($state, '_'));

                // Fill slug if empty / operation is create
                if ($operation === 'create' || empty($get('title'))) {
                    $set('title', $state);
                }
            })
            ->unique(
                table: InspireCmsConfig::getFieldGroupModelClass(),
                column: 'name',
                ignoreRecord: true
            );
    }
}
