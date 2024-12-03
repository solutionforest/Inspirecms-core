<?php

namespace SolutionForest\InspireCms\Fields;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;

/**
 * @mixin \SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig
 */
class TranslatableFieldType
{
    public function getEnhancedFormSchema()
    {
        return function () {
            return [
                Section::make()
                    ->schema([
                        Toggle::make('translatable')->default(false)->inlineLabel()->onIcon('heroicon-m-language'),
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
