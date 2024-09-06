<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Support\Facades\DB;
use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Models\Contracts\Language as LanguageContract;

class Language extends BaseModel implements LanguageContract
{
    protected $guarded = ['id'];

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->name;
    }

    public function isDefault(): bool
    {
        return $this->is_default;
    }

    public static function findOrCreateDefaultLanguage(): LanguageContract
    {
        $locale = config('app.locale', 'en');

        // Create if not exists
        /**
         * @var LanguageContract
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
