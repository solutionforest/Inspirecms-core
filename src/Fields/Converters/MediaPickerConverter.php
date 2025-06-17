<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;
use SolutionForest\InspireCms\Support\Dtos\MediaAssetDto;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class MediaPickerConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        $value = $this->applyLocaleConversion($sourceValue, $locale, $fallbackLocale);

        if (is_null($value)) {
            return [];
        }

        $formattedSourceValue = is_array($value) ? $value : [$value];

        return collect($formattedSourceValue)
            ->map(function ($item) {
                if (is_array($item)) {
                    return MediaAssetDto::fromArray($item);
                } elseif ($item instanceof MediaAsset) {
                    return MediaAssetDto::fromModel($item);
                } elseif ($item instanceof BaseDto) {
                    return $item;
                }

                return null;
            })
            ->reject(fn ($item) => is_null($item))
            ->all();
    }
}
