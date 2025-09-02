<?php

namespace SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components;

use Filament\Forms\Components\Select;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;

class FieldTypeInput
{
    public static function make(): Select
    {
        return Select::make('type')
            ->label(__('inspirecms::resources/field.type.label'))
            ->validationAttribute(__('inspirecms::resources/field.type.validation_attribute'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::resources/field.type.label'))
            ->helperText(__('inspirecms::resources/field.type.instructions'))
            ->columns(4)
            ->searchable()
            ->options(fn () => FieldTypeHelper::getFieldTypeOptions())
            ->getSearchResultsUsing(fn ($search) => FieldTypeHelper::getFieldTypeOptions($search))
            ->preload()
            ->allowHtml()
            ->required()
            ->columnSpan('full')
            ->live(debounce: 500)
            ->afterStateUpdated(fn (Select $component) => $component
                ->getContainer()
                ->getParentComponent()->getContainer() // section
                ->getComponent('configFields')
                ?->getChildComponentContainer()
                ?->fill());
    }
}
