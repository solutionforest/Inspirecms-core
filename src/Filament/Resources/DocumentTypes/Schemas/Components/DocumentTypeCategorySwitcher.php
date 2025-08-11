<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypes\Schemas\Components;

use Filament\Forms\Components\ToggleButtons;
use SolutionForest\InspireCms\Base\Enums\Interfaces\DocumentTypeCategory;
use SolutionForest\InspireCms\InspireCmsConfig;

class DocumentTypeCategorySwitcher
{
    public static function make()
    {
        $modelFqcn = InspireCmsConfig::getDocumentTypeModelClass();
        $enumClass = $modelFqcn::getCategoryEnumClass();

        return ToggleButtons::make('category')
            ->label(__('inspirecms::resources/document-type.category.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.category.validation_attribute'))
            ->inline()
            ->grouped()
            ->options($enumClass)
            ->default($enumClass::getDefaultValue()->value)
            ->required()
            ->live()
            ->colors(collect($enumClass::cases())->mapWithKeys(fn (DocumentTypeCategory $enumClass): array => [$enumClass->value => $enumClass->getColor()])->all())
            ->helperText(function ($state) use ($enumClass) {
                if (! $state) {
                    return null;
                }

                $enum = ($state instanceof DocumentTypeCategory) ? $state : $enumClass::tryFrom($state);

                if ($enum) {
                    return $enum->getDescription();
                }

                return null;
            });
    }
}
