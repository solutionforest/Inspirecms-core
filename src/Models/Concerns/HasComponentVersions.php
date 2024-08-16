<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

trait HasComponentVersions
{
    public static function bootHasComponentVersions()
    {
        //
    }

    public function versions(): MorphMany
    {
        return $this->morphMany(InspireCmsConfig::getComponentVersionModelClass(), 'component');
    }

    public function latestVersion(): MorphOne
    {
        return $this->morphOne(InspireCmsConfig::getComponentVersionModelClass(), 'component')->ofMany('version_date', 'max');
    }
}
