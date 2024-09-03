<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Support\Facades\DB;
use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Models\Contracts\Language as CmsLanguageContract;

class Language extends BaseModel implements CmsLanguageContract
{
    protected $guarded = ['id'];

    public static function findOrCreateDefaultLanguage(): CmsLanguageContract
    {
        $locale = config('app.locale', 'en');

        // Create if not exists
        /** 
         * @var CmsLanguageContract
         */
        $result = static::query()->firstOrCreate(
            ['code' => $locale],
            [
                'name' => locale_get_display_name($locale) ?? $locale,
                'is_default' => true,
            ]
        );

        return $result;
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            // Set "is_default" of other languages as false if this model is changing to "default"
            if ($model->isDirty(['is_default']) && $model->is_default) {
                DB::transaction(function () use ($model) {
                    static::query()
                        ->where('is_default', true)
                        ->whereKeyNot($model->getKey())
                        ->update(['is_default' => false]);
                });
            }
        });
    }
}
