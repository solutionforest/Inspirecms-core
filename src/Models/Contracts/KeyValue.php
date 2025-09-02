<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $key
 * @property ?string $value
 * @property ?CarbonInterface $created_at
 * @property ?CarbonInterface $updated_at
 */
interface KeyValue
{
    /**
     * Find the value associated with the given key.
     *
     * @param  string  $key  The key to search for.
     * @return null|Model|KeyValue
     */
    public static function findKeyValue($key);

    /**
     * Set a key-value pair.
     *
     * @param  string  $key  The key to set.
     * @param  mixed  $value  The value to associate with the key.
     * @return null|Model|KeyValue
     */
    public static function setKeyValue($key, $value);
}
