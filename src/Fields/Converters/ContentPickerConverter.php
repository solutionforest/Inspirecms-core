<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Collection\ContentCollection;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

class ContentPickerConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        $value = $this->applyLocaleConversion($sourceValue, $locale, $fallbackLocale);


        if (is_null($value)) {
            return [];
        }

        $formattedSourceValue = is_array($value) ? $value : [$value];

        $records = $this->getContentRecords($formattedSourceValue);

        $convertedValue = collect($formattedSourceValue)
            ->map(function ($item) use ($records, $locale) {

                try {
                    if (is_string($item)) {
                        return $records->get($item)?->toDto($locale);
                    } elseif ($item instanceof Model) {
                        return $item->toDto($locale);
                    } elseif ($item instanceof BaseDto) {
                        return $item;
                    }
                } catch (\Throwable $th) {
                    //
                }

                return null;
            })
            ->reject(fn ($item) => is_null($item))
            ->all();

        if ($this->isFieldTypeTranslatable()) {
            ray($value, $sourceValue, $convertedValue)->label($this->getFieldIdentifier())->blue();
        }

        return $convertedValue;
    }

    /**
     * @return Collection<Model>|ContentCollection
     */
    private function getContentRecords(array $sourceValue)
    {
        $keysToFind = collect($sourceValue)->flatten()->where(fn ($v) => is_string($v))->unique()->filter()->values()->all();

        if (empty($keysToFind)) {
            return collect();
        }

        return inspirecms_content()
            ->findByIds(
                ids: $keysToFind,
                isPublished: true,
                limit: count($keysToFind),
            )
            ->mapWithKeys(fn ($record) => [$record->getKey() => $record]);
    }
}
