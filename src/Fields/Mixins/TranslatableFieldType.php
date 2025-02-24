<?php

namespace SolutionForest\InspireCms\Fields\Mixins;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Translatable;

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

    public function isFieldTypeTranslatable()
    {
        return function () {
            $translatable = collect($this->getTargetAttributes(Translatable::class))
                ->map(fn (Translatable $attribute) => $attribute->translatable)
                ->first();

            if (is_null($translatable)) {
                return true;
            }

            return $translatable;
        };
    }
}
