<?php

namespace SolutionForest\InspireCms\Helpers;

use Illuminate\Support\Arr;
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;

class FieldTypeHelper
{
    public static function performFormFieldFromConfig(string $typeName, \Closure $createFieldUsing, array $config = [])
    {

        $fiFormConfig = static::getFieldTypeConfig($typeName, $config);

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

    public static function getFieldTypeConfig(string $typeName, array $config = []): ?FieldTypeConfig
    {
        return FilamentFieldGroup::getFieldTypeConfig($typeName, $config);
    }

    public static function getFieldTypeOptions(?string $search = null, array $excepts = []): array
    {
        $options = FilamentFieldGroup::getFieldTypeOptions();

        if (filled($search) && ! is_null($search)) {
            $options = Arr::where(
                $options,
                fn ($item) => str_contains($item['name'], $search)
            );
        }

        if (!empty($excepts)) {
            $options = Arr::where(
                $options,
                fn ($item) => !in_array($item['name'], $excepts)
            );
        }

        return collect($options)
            ->groupBy('group')
            ->map(fn ($collection) => collect($collection)->mapWithKeys(function ($item) {

                $icon = $item['icon'] ?? null;
                $label = $item['display'] ?? $item['name'] ?? '';

                $textWithIconHtml = UIHelper::generateTextWithIcon($label, $icon, 'gray');

                return [$item['name'] => $textWithIconHtml->toHtml()];
            }))
            ->toArray();
    }
}
