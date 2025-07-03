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

        // fetch media assets from the database using primary keys (In Preview mode)
        $mediaAssetIds = collect($formattedSourceValue)->where(fn ($v) => is_string($v));
        
        $mediaAssets = (count($mediaAssetIds) > 0) ? 
            collect(inspirecms_asset()->findByKeys($mediaAssetIds->all()))->mapWithKeys(fn ($item) => [$item->getKey() => $item]) : 
            collect();

        return collect($formattedSourceValue)
            ->map(function ($item) use ($mediaAssets) {
                if (is_array($item)) {
                    return MediaAssetDto::fromArray($item);
                } elseif ($item instanceof MediaAsset) {
                    return MediaAssetDto::fromModel($item);
                } elseif ($item instanceof BaseDto) {
                    return $item;
                } elseif (is_string($item)) {
                    $model = $mediaAssets->get($item);
                    if ($model) {
                        return MediaAssetDto::fromModel($model);
                    }
                }

                return null;
            })
            ->reject(fn ($item) => is_null($item))
            ->all();
    }
}
