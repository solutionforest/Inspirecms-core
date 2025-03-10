<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Language as LanguageContract;
use SolutionForest\InspireCms\Observers\LanguageObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;

class Language extends BaseModel implements LanguageContract
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function contentRoutes()
    {
        return $this->hasMany(InspireCmsConfig::getContentRouteModelClass(), 'language_id');
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getLabel($displayLocale = null)
    {
        $result = locale_get_display_name($this->getCode(), $displayLocale);

        if ($result === false) {
            return $this->getCode();
        }

        return $result;
    }

    public function isDefault()
    {
        return $this->is_default;
    }

    public static function boot()
    {
        parent::boot();

        static::observe(LanguageObserver::class);
    }
}
