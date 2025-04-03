<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class MediaPickerConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        // todo: improve performance

        if (is_null($sourceValue)) {
            return [];
        }

        $formattedSourceValue = is_array($sourceValue) ? $sourceValue : [$sourceValue];

        $keysToFind = collect($formattedSourceValue)->where(fn ($v) => is_string($v));

        $mediaAssets = $keysToFind->isNotEmpty()
            ? inspirecms_asset()->findByKeys($keysToFind->all())->mapWithKeys(fn (Model $record) => [$record->getKey() => $record])
            : collect();

        return $mediaAssets
            ->filter(fn ($c) => in_array($c->getKey(), $formattedSourceValue))
            ->sortBy(fn ($c) => array_search($c->getKey(), $formattedSourceValue))
            ->values()
            ->map(function ($item) use ($locale) {
                if ($item instanceof MediaAsset) {
                    return $item->toDto($locale);
                } elseif ($item instanceof BaseDto) {
                    return $item;
                }

                return null;
            })
            ->reject(fn ($item) => is_null($item))
            ->all();
    }
}
