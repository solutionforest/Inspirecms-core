<?php

namespace SolutionForest\InspireCms\Helpers;

use Illuminate\Support\Arr;
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;

class FieldTypeHelper
{
    public static function performFormFieldFromConfig(string $typeName, \Closure $createFieldUsing, array $config = [])
    {
        
        $fiFormConfig = FilamentFieldGroup::getFieldTypeConfig($typeName, $config);

        if (! $fiFormConfig) {
            return null;
        }

        $fiFormComponentFQCN = Arr::first(Arr::pluck($fiFormConfig->getFormComponents(), 'component'));
        if (! $fiFormComponentFQCN) {
            throw new \Exception("The field type config class {$fiFormConfig} does not have a FormComponent attribute.");
        }

        $fiFormComponent = $createFieldUsing($fiFormConfig, $fiFormComponentFQCN);
        if (! $fiFormComponent) {
            throw new \Exception("The field type config class {$fiFormConfig} does not have a FormComponent attribute.");
        }

        $fiFormConfig->applyConfig($fiFormComponent);

        return $fiFormComponent;
    }
}
