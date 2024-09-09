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
        $templateId = $template instanceof Template ? $template->getId() : $template;

        $this->templates()
            ->updateExistingPivot($templateId, ['is_default' => true], false);

        $this->templates()->whereKeyNot($templateId)->update([
            'is_default' => false,
        ]);
    }
}
