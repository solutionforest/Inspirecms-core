<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class CmsLanauage extends Model
{
    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(InspireCmsConfig::getLanguageTableName());
    }

    public static function findOrCreateDefaultLanguage()
    {
        $locale = config('app.locale', 'en');

        // Create if not exists
        return static::query()->firstOrCreate(
            ['code' => $locale],
            [
                'name' => locale_get_display_name($locale) ?? $locale,
                'is_default' => true,
            ]
        );
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
           // Set "is_default" of other languages as false if this model is changing to "default"
            if ($model->isDirty(['is_default']) && $model->is_default) {
                \DB::transaction(function () use ($model) {
                    static::query()
                        ->where('is_default', true)
                        ->whereKeyNot($model->getKey())
                        ->update(['is_default' => false]);
                });
            }
        });
    }
}
