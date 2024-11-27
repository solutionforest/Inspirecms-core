<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Template;

trait HasTemplates
{
    public function templates()
    {
        return $this->morphToMany(InspireCmsConfig::getTemplateModelClass(), 'templateable', InspireCmsConfig::getTemplateableTableName())
            ->withPivot(['is_default']);
    }

    public function templateable()
    {
        return $this->morphMany(InspireCmsConfig::getTemplateableModelClass(), 'templateable');
    }

    /** @inheritDoc*/
    public function setAsDefaultTemplate($template)
    {
        $templateId = $template instanceof Template ? $template->getKey() : $template;

        $this->templateable()
            ->where('template_id', $templateId)
            ->update(['is_default' => true]);

        $this->templateable()
            ->where('template_id', '!=', $templateId)
            ->update(['is_default' => false]);
    }

    public function getDefaultTemplate()
    {
        if ($this->relationLoaded('templates')) {
            return $this->templates->first(fn (Template $template) => $template->pivot->is_default);
        }

        return $this->templates()->wherePivot('is_default', true)->first();
    }
}
