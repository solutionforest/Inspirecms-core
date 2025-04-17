<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use SolutionForest\InspireCms\Dtos\Collection\PropertyDataCollection;
use SolutionForest\InspireCms\Dtos\PropertyDataDto;
use SolutionForest\InspireCms\Dtos\PropertyDataGroupDto;
use SolutionForest\InspireCms\Dtos\PropertyTypeDto;
use SolutionForest\InspireCms\Fields\Configs\Repeater;
use SolutionForest\InspireCms\Fields\PropertyValueTransformerInterface;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;

class RepeaterConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        $fieldTypeConfig = $this->getFieldTypeConfig();
        if (! $fieldTypeConfig instanceof Repeater) {
            return [];
        }
        $fieldConfigForInnerItems = $this->getFieldConfigForInnerItems($fieldTypeConfig);

        if (! is_array($sourceValue)) {
            $sourceValue = [];
        }

        $convertedValues = [];

        foreach ($sourceValue as $repeaterItemKey => $repeaterItemData) {

            // Already converted
            if ($repeaterItemData instanceof PropertyDataGroupDto) {
                $convertedValues[$repeaterItemKey] = $repeaterItemData;

                continue;
            }

            $propDataForItem = PropertyDataCollection::make();
            foreach ($repeaterItemData as $key => $value) {

                $innerPropertyType = $fieldConfigForInnerItems[$key] ?? null;
                if (is_null($innerPropertyType)) {
                    continue;
                }

                $innerFieldConverter = $this->tryGetConverterForInnerField(
                    $innerPropertyType,
                    implode('.', [$this->getFieldIdentifier(), $repeaterItemKey]),
                    $key
                );
                if (is_null($innerFieldConverter)) {
                    continue;
                }

                $innerPropTypeDto = PropertyTypeDto::fromArray([
                    'key' => $key,
                    'group' => $repeaterItemKey,
                    'config' => $innerPropertyType,
                ]);
                $newInnerValue = PropertyDataDto::fromArray([
                    'key' => $key,
                    // raw value for inner field
                    'value' => $value,
                    'propertyType' => $innerPropTypeDto,
                ])->setFallbackLocale($fallbackLocale);

                $propDataForItem->push($newInnerValue);

            }

            $convertedValues[$repeaterItemKey] = PropertyDataGroupDto::fromArray([
                'key' => $repeaterItemKey,
                'data' => $propDataForItem,
                'propertyTypes' => collect($propDataForItem)
                    ->mapWithKeys(fn ($p) => [
                        $p->key => $p->propertyType,
                    ]),
            ]);
        }

        return $convertedValues;
    }

    private function getFieldConfigForInnerItems(Repeater $fieldTypeConfig): array
    {
        return collect($fieldTypeConfig->fields)
            ->reject(fn ($item) => ! is_array($item))
            ->reject(
                fn (array $item) => ! isset($item['field']) || blank($item['field']) ||
                ! isset($item['name']) || blank($item['name'])
            )
            ->keyBy('name')
            // array -> convert to -> FieldTypeConfig
            ->map(
                fn (array $item) => FieldTypeHelper::getFieldTypeConfig($item['field'], $item['fieldConfig'] ?? [])
            )
            ->all();
    }

    private function tryGetConverterForInnerField($fieldTypeConfig, $group, $key): ?BaseConverter
    {
        try {

            $transformer = app(PropertyValueTransformerInterface::class);

            return $transformer->getConverter($fieldTypeConfig, $key, $group);

        } catch (\Throwable $th) {

            return null;

        }
    }
}
