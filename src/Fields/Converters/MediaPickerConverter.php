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
        $result = [];

        if (is_null($sourceValue)) {
            return $result;
        }

        $keysToFind = collect($sourceValue)->where(fn ($v) => is_string($v));
        $mediaAssets = $keysToFind->isNotEmpty()
            ? inspirecms_asset()->findByKeys($keysToFind->all())->mapWithKeys(fn (Model $record) => [$record->getKey() => $record])
            : collect();

        foreach ($sourceValue as $index => $item) {

            if ($item instanceof MediaAsset) {
                $result[] = $item->toDto($locale);
            } elseif ($item instanceof BaseDto) {
                $result[] = $item;
            } elseif (is_string($item)) {
                if (($mediaAsset = $mediaAssets->get($item)) instanceof MediaAsset) {
                    $result[] = $mediaAsset->toDto($locale);
                } else {
                    $result[] = null;
                }
            } else {
                $result[] = null;
            }
        }

        return $result;
    }
}
