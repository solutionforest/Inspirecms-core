<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\InspireCmsConfig;

class AssetService implements AssetServiceInterface
{
    /** {@inheritDoc} */
    public function findByKey(string | int $key)
    {
        return $this->getQuery()->with('media')->find($key);
    }

    /** {@inheritDoc} */
    public function findByKeys(...$keys)
    {
        $keys = array_filter(Arr::flatten($keys));

        if (count($keys) === 0) {
            return collect();
        }

        return $this->getQuery()->with('media')->findMany($keys);
    }

    // region Helpers
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getQuery()
    {
        return static::getModel()::query();
    }

    /**
     * @return class-string<Model>
     */
    protected static function getModel()
    {
        return InspireCmsConfig::getMediaAssetModelClass();
    }
    // endregion Helpers
}
