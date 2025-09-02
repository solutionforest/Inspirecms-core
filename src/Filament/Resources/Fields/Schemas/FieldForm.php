<?php

namespace SolutionForest\InspireCms\Filament\Resources\Fields\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components\FieldInstructionsInput;
use SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components\FieldLabelInput;
use SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components\FieldMandatoryToggle;
use SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components\FieldNameInput;
use SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components\FieldStatePathInput;
use SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components\FieldTypeInput;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;

class FieldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        FieldLabelInput::make(),
                        FieldNameInput::make(),
                        FieldInstructionsInput::make(),
                        FieldTypeInput::make(),
                    ]),

                Section::make()
                    ->schema([
                        FieldMandatoryToggle::make()->columnSpanFull(),
                    ]),

                static::getConfigFormComponent(),

                Group::make([
                    Hidden::make('id'),
                    Hidden::make('group_id'),
                    Hidden::make('sort'),
                    FieldStatePathInput::make()->hidden(),
                ])->hidden()->dehydratedWhenHidden(true),
            ]);
    }

    public static function getConfigFormComponent($key = 'configFields', $statePath = 'config'): Group
    {
        return Group::make()
            ->key($key)
            ->statePath($statePath)
            ->schema(fn (Get $get) => FieldTypeHelper::getFieldConfigFormSchemaForFieldType($get('type')));
    }
}
