<?php

namespace SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas;

use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\Components\FieldGroupActiveToggle;
use SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\Components\FieldGroupFieldsRepeater;
use SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\Components\FieldGroupNameInput;
use SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\Components\FieldGroupTitleInput;

class FieldGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Wizard::make(static::getStepsSchema())->skippable(),
            ]);
    }

    public static function getStepsSchema(): array
    {
        return [
            Step::make('fields')
                ->label(__('inspirecms::resources/field-group.steps.fields.label'))
                ->schema([
                    FieldGroupFieldsRepeater::make()
                        ->hiddenLabel(),
                ]),
            Step::make('settings')
                ->label(__('inspirecms::resources/field-group.steps.settings.label'))
                ->schema([
                    FieldGroupNameInput::make(),
                    FieldGroupTitleInput::make(),
                    FieldGroupActiveToggle::make(),
                ]),
        ];
    }
}
