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

        $fieldTypeConfig = $this->fieldTypeConfig;

        if (! $fieldTypeConfig instanceof Repeater) {
            return [];
        }

        if (! is_array($sourceValue)) {
            $sourceValue = [];
        }

        $newValue = [];

        foreach ($sourceValue as $i => $data) {

            $propData = PropertyDataCollection::make();

            foreach ($data as $key => $value) {

                $field = collect($fieldTypeConfig->fields)->firstWhere('name', $key);

                if (is_null($field) || ! isset($field['field']) || blank($field['field'])) {
                    continue;
                }

                $innerFieldTypeName = $field['field'];

                $innerPropertyType = FieldTypeHelper::getFieldTypeConfig($innerFieldTypeName, $field['fieldConfig'] ?? []);
                if (is_null($innerPropertyType)) {
                    continue;
                }

                $innerFieldConverter = $this->tryGetConverterForInnerField($innerPropertyType);
                if (is_null($innerFieldConverter)) {
                    continue;
                }

                $finalValue = null;
                $attempt = $this->tryGetDisplayValueForInnerField($innerFieldConverter, $value, $locale, $fallbackLocale, $finalValue);

                $innerPropTypeDto = PropertyTypeDto::fromArray([
                    'key' => $key,
                    'group' => $i,
                    'config' => $innerPropertyType,
                ]);
                $newInnerValue = PropertyDataDto::fromArray([
                    'key' => $key,
                    'value' => $finalValue,
                    'propertyType' => $innerPropTypeDto,
                ])
                    ->setFallbackLocale($fallbackLocale);

                $propData->push($newInnerValue);

            }

            $newValue[$i] = PropertyDataGroupDto::fromArray([
                'key' => $i,
                'data' => $propData,
                'propertyTypes' => collect($propData)
                    ->mapWithKeys(fn ($p) => [
                        $p->key => $p->propertyType,
                    ]),
            ]);
        }

        return $newValue;
    }

    /**
     * @param  BaseConverter  $convert
     * @param  mixed  $sourceValue
     * @param  ?string  $locale
     * @param  ?string  $fallbackLocale
     * @param  mixed  $finalValue
     * @return bool
     */
    private function tryGetDisplayValueForInnerField($convert, $sourceValue, $locale, $fallbackLocale, &$finalValue = null)
    {
        try {

            $finalValue = $convert->toDisplayValue($sourceValue, $locale, $fallbackLocale);

            return true;

        } catch (\Throwable $th) {

            return false;

        }
    }

    private function tryGetConverterForInnerField($fieldTypeConfig): ?BaseConverter
    {
        try {

            $transformer = app(PropertyValueTransformerInterface::class);

            return $transformer->getConverter($fieldTypeConfig);

        } catch (\Throwable $th) {

            return null;

        }
    }
}
