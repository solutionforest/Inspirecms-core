<?php

namespace SolutionForest\InspireCms\Filament\Resources\Roles\Schemas\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Validation\Rules\Unique;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class RoleNameInput
{
    public static function make(): TextInput
    {
        return TextInput::make('name')
            ->label(__('inspirecms::resources/role.name.label'))
            ->validationAttribute(__('inspirecms::resources/role.name.validation_attribute'))
            ->unique(table: InspireCmsConfig::getRoleModelClass(), column: 'name', ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                return $rule->where('guard_name', AuthHelper::guardName());
            })
            ->required();
    }
}
