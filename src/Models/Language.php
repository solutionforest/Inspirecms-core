<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\InspireCms\Models\Contracts\Language as LanguageContract;
use SolutionForest\InspireCms\Observers\LanguageObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;

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

        static::observe(LanguageObserver::class);
    }
}
