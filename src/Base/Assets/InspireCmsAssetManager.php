<?php

namespace SolutionForest\InspireCms\Base\Assets;

use Illuminate\Cache\CacheManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\InspireCmsConfig;

class InspireCmsAssetManager implements InspireCmsAssetManagerInterface
{
    protected CacheManager $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @return ?Model
     */
    public function findByKey(string | int $key)
    {
        return InspireCmsConfig::getMediaAssetModelClass()::with('media')->find($key);
    }

    /**
     * @param  string|int  ...$keys
     * @return ?Model
     */
    public function findByKeys(...$keys)
    {
        $keys = Arr::flatten($keys);
        return InspireCmsConfig::getMediaAssetModelClass()::with('media')->findMany($keys);
    }

    public function getAssetUrl(Model | string | int $asset): string
    {
        try {
            return route('inspirecms.asset', ['key' => $asset instanceof Model ? $asset->getKey() : $asset]);
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $asset->media->getUrl();
    }

    public function getAssetMiddleware(): array
    {
        return [
            'cache.headers:public;max_age=2628000;etag',
        ];
    }
}
