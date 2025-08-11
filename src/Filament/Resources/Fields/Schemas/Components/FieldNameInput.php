<?php

namespace SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use SolutionForest\InspireCms\InspireCmsConfig;

class FieldNameInput
{
    public static function make(): TextInput
    {
        return TextInput::make('name')
            ->label(__('inspirecms::resources/field.name.label'))
            ->validationAttribute(__('inspirecms::resources/field.name.validation_attribute'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::resources/field.name.label'))
            ->helperText(__('inspirecms::resources/field.name.instructions'))
            ->required()
            ->maxLength(255)
            ->live(debounce: 500)
            ->afterStateUpdated(fn ($component, ?string $state) => $component->state(Str::slug($state, '_')))
            ->unique(table: InspireCmsConfig::getFieldModelClass(), column: 'name', ignorable: function ($component, $get) {
                $id = $get('id');

                return InspireCmsConfig::getFieldModelClass()::find($id);
            }, modifyRuleUsing: function (Unique $rule, ?Model $record, $get) {
                $groupId = $record?->group_id ?? $get('group_id') ?? 0;

                return $rule->where('group_id', $groupId);
            });
    }
}
