<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use SolutionForest\InspireCms\Models\Polymorphic\CmsComponentVersion;

trait HasComponentVersions
{
    public function versions(): MorphMany
    {
        return $this->morphMany($this->getCmsComponentVersionModel(), 'component');
    }

    public function latestVersion(): MorphOne
    {
        return $this->morphOne($this->getCmsComponentVersionModel(), 'component')->ofMany('version_date', 'max');
    }

    protected function getCmsComponentVersionModel(): string
    {
        return config('inspirecms-core.models.component_version.fqcn', CmsComponentVersion::class);
    }
}
