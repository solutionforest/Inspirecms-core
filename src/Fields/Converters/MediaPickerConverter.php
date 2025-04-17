<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class MediaPickerConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        $value = $this->applyLocaleConversion($sourceValue, $locale, $fallbackLocale);

        // todo: improve performance

        if (is_null($value)) {
            return [];
        }

        $formattedSourceValue = is_array($value) ? $value : [$value];

        $records = $this->getMediaAssetRecords($value);

        return collect($formattedSourceValue)
            ->map(function ($item) use ($records, $locale) {
                if (is_string($item)) {
                    return $records->get($item)?->toDto($locale);
                } elseif ($item instanceof MediaAsset) {
                    return $item->toDto($locale);
                } elseif ($item instanceof BaseDto) {
                    return $item;
                }

                return null;
            })
            ->reject(fn ($item) => is_null($item))
            ->all();
    }

    /**
     * @return Collection<Model>
     */
    private function getMediaAssetRecords(array $sourceValue)
    {
        $keysToFind = collect($sourceValue)->flatten()->where(fn ($v) => is_string($v))->unique()->filter()->values()->all();

        if (empty($keysToFind)) {
            return collect();
        }
        
        return inspirecms_asset()
            ->findByKeys($keysToFind)
            ->mapWithKeys(fn (Model $record) => [$record->getKey() => $record]);
    }
}
