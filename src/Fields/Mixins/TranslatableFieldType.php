<?php

namespace SolutionForest\InspireCms\Fields\Mixins;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;

class TranslatableFieldType
{
    public function getEnhancedFormSchema()
    {
        return function () {
            return [
                Section::make()
                    ->schema([
                        Toggle::make('translatable')
                            ->label(__('inspirecms::resources/field.translatable.label'))
                            ->validationAttribute(__('inspirecms::resources/field.translatable.validation_attribute'))
                            ->default(false)
                            ->inlineLabel()
                            ->onIcon('heroicon-m-language'),
                    ]),
                ...$this->getFormSchema(),
            ];
        };
    }

    public function isTranslatable()
    {
        return function () {
            return isset($this->translatable) && $this->translatable === true;
        };
    }
}
