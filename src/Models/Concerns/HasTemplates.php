<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\InspireCms\Models\Contracts\Template;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

trait HasTemplates
{
    public function templates(): MorphToMany
    {
        return $this->morphToMany(InspireCmsConfig::getTemplateModelClass(), 'templateable', InspireCmsConfig::getTemplateableTableName())
            ->withPivot(['is_default']);
    }

    public function templatable(): MorphMany
    {
        return $this->morphMany(InspireCmsConfig::getTemplateableModelClass(), 'templateable');
    }

    public function setAsDefaultTemplate(Template | string | int $template): void
    {
        $templateId = $template instanceof Template ? $template->getKey() : $template;

        $this->templatable()
            ->where('template_id', $templateId)
            ->update(['is_default' => true]);

        $this->templatable()
            ->where('template_id', '!=', $templateId)
            ->update(['is_default' => false]);
    }

    public function getDefaultTemplate(): ?Template
    {
        return $this->templates()->wherePivot('is_default', true)->first();
    }
}
