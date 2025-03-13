<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void cacheAll() Cache all key-value pairs
 * @method static mixed get(string $key, mixed $default = null) Get a value by key from cache or database
 * @method static void set(string $key, mixed $value) Set a value in the cache
 * @method static void forget(string $key) Delete a value from the cache
 * @method static void clear() Clear all key-value cache
 *
 * @see \SolutionForest\InspireCms\Base\KeyValueCache
 */
class KeyValueCache extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return \SolutionForest\InspireCms\Base\KeyValueCache::class;
    }
}
