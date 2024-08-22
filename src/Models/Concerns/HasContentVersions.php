<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

trait HasContentVersions
{
    public static function bootHasContentVersions()
    {
        //
    }

    public function contentVersions(): HasMany
    {
        return $this->hasMany(InspireCmsConfig::getContentVersionModelClass(), 'content_id');
    }
}
