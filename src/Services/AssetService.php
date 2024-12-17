<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Cache\CacheManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\InspireCmsConfig;

class AssetService implements AssetServiceInterface
{
    // todo: performance improvement
    // protected CacheManager $cacheManager;

    // public function __construct(CacheManager $cacheManager)
    // {
    //     $this->cacheManager = $cacheManager;
    // }

    /** {@inheritDoc} */
    public function findByKey(string | int $key)
    {
        return static::getModel()::with('media')->find($key);
    }

    /** {@inheritDoc} */
    public function findByKeys(...$keys)
    {
        $keys = Arr::flatten($keys);

        return static::getModel()::with('media')->findMany($keys);
    }

    //region Helpers
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
    //endregion Helpers
}
