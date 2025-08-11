<?php

namespace SolutionForest\InspireCms\Base;

use DateInterval;
use Illuminate\Cache\CacheManager;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Helpers\ModelHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\KeyValue;
use Throwable;

class KeyValueCache
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /** @var DateInterval|int */
    protected $cacheExpirationTime;

    protected $cacheStore = null;

    protected $keyPrefix = null;

    /**
     * @param  CacheManager  $cacheManager
     */
    public function __construct($cacheManager, DateInterval | int | null $ttl = null)
    {
        $this->cacheManager = $cacheManager;
        $this->cacheExpirationTime = $ttl ?? InspireCmsConfig::get('cache.key_value.ttl') ?? DateInterval::createFromDateString('24 hours');
        $this->cacheStore = InspireCmsConfig::get('cache.key_value.store') ?? null;
        $this->keyPrefix = InspireCmsConfig::get('cache.key_value.prefix') ?? null;
    }

    /**
     * Cache all key-value pairs
     */
    public function cacheAll(): void
    {
        $model = $this->attemptRetrieveModel();

        if (! $model) {
            return;
        }

        $keyValues = $model::all();

        foreach ($keyValues as $keyValue) {
            $this->set($keyValue->key, $keyValue->value);
        }
    }

    /**
     * Get a value by key from cache or database
     *
     * @param  mixed  $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $model = $this->attemptRetrieveModel();

        if (! $model) {
            return;
        }

        return $this->cacheManager
            ->store($this->cacheStore)
            ->remember(
                $this->getCacheKey($key),
                $this->cacheExpirationTime,
                function () use ($key, $default, $model) {
                    $keyValue = $model::findKeyValue($key);

                    return $keyValue ? $keyValue->value : $default;
                }
            );
    }

    /**
     * Set a value in the cache
     *
     * @param  mixed  $value
     */
    public function set(string $key, $value): void
    {
        $this->cacheManager
            ->store($this->cacheStore)
            ->put($this->getCacheKey($key), $value);
    }

    /**
     * Delete a value from the cache
     */
    public function forget(string $key): void
    {
        $this->cacheManager
            ->store($this->cacheStore)
            ->forget($this->getCacheKey($key));
    }

    /**
     * Clear all key-value cache
     */
    public function clear(): void
    {
        $model = $this->attemptRetrieveModel();

        if (! $model) {
            return;
        }

        $keyValues = $model::all();

        foreach ($keyValues as $keyValue) {
            $this->forget($keyValue->key);
        }
    }

    /**
     * Get the cache key with prefix
     */
    protected function getCacheKey(string $key): string
    {
        return $this->keyPrefix . $key;
    }

    /**
     * @return bool|class-string<Model & KeyValue>
     */
    protected function attemptRetrieveModel(): bool | string
    {
        try {
            $model = InspireCmsConfig::getKeyValueModelClass();

            $table = $model::make()->getTable();

            if (ModelHelper::isTableExists($table)) {
                return $model;
            }

            return false;

        } catch (Throwable $th) {
            return false;
        }
    }
}
