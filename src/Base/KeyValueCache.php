<?php

namespace SolutionForest\InspireCms\Base;

use Illuminate\Cache\CacheManager;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Helpers\ModelHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\KeyValue;

class KeyValueCache
{
    /**
     * Cache prefix for key-value pairs
     */
    protected const CACHE_PREFIX = 'inspire_key_value_';

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /** @var \DateInterval|int */
    protected $cacheExpirationTime;

    /**
     * @param CacheManager $cacheManager
     */
    public function __construct($cacheManager, \DateInterval|int|null $ttl = null)
    {
        $this->cacheManager = $cacheManager;
        $this->cacheExpirationTime = $ttl ?? InspireCmsConfig::get('cache.key_value.ttl') ?? \DateInterval::createFromDateString('24 hours');
    }

    /**
     * Cache all key-value pairs
     */
    public function cacheAll(): void
    {
        $model = $this->attemptRetrieveModel();

        if (!$model) {
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
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $model = $this->attemptRetrieveModel();

        if (!$model) {
            return;
        }

        return $this->cacheManager->remember(
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
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $this->cacheManager->put($this->getCacheKey($key), $value);
    }

    /**
     * Delete a value from the cache
     */
    public function forget(string $key): void
    {
        $this->cacheManager->forget($this->getCacheKey($key));
    }

    /**
     * Clear all key-value cache
     */
    public function clear(): void
    {
        $model = $this->attemptRetrieveModel();

        if (!$model) {
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
        return self::CACHE_PREFIX . $key;
    }

    /**
     * @return bool|class-string<Model & KeyValue>
     */
    protected function attemptRetrieveModel(): bool|string
    {
        try {
            $model = InspireCmsConfig::getKeyValueModelClass();

            $table = $model::make()->getTable();

            if (ModelHelper::isTableExists($table)) {
                return $model;
            }

            return false;

        } catch (\Throwable $th) {
            return false;
        }
    }
}