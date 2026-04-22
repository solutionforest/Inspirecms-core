<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\InspireCms\Models\Contracts\KeyValue as KeyValueContract;
use SolutionForest\InspireCms\Observers\KeyValueObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;

class KeyValue extends BaseModel implements KeyValueContract
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public static function findKeyValue($key)
    {
        return static::find($key);
    }

    public static function setKeyValue($key, $value)
    {
        return static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function booted()
    {
        parent::booted();

        static::observe(KeyValueObserver::class);
    }
}
