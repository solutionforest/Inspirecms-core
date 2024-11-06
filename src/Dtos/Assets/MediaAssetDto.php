<?php

namespace SolutionForest\InspireCms\Dtos\Assets;

use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

class MediaAssetDto extends BaseDto
{
    /**
     * @var array
     */
    public $keys;

    public function getDetails(): array
    {
        if (blank($this->keys) || ! is_array($this->keys) || is_null($this->keys)) {
            return [];
        }
        $assets = inspirecms_asset()->findByKeys($this->keys);

        return collect($assets)->map(function ($asset) {
            return $asset->toDto();
        })->all();
    }
}
