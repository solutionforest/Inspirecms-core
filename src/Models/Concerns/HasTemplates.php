<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

trait HasTemplates
{
    public function templates(): MorphToMany
    {
        return $this->morphToMany(InspireCmsConfig::getTemplateModelClass(), 'templateable', InspireCmsConfig::getTemplateableTableName())
            ->withPivot(['is_default']);
    }

    public function defaultTemplate(): MorphOne
    {
        return $this->morphOne(InspireCmsConfig::getTemplateModelClass(), 'templateable', InspireCmsConfig::getTemplateableTableName())
            ->ofMany('is_default', true)
            ->withPivot(['is_default']);
    }

    public function templatable(): MorphMany
    {
        return $this->morphMany(InspireCmsConfig::getTemplateableModelClass(), 'templateable');
    }
}
