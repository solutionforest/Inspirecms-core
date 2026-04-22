<?php

namespace SolutionForest\InspireCms\Models\Contracts\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Models\Contracts\Template;
use SolutionForest\InspireCms\Models\Contracts\Templateable;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<Model & Template> $templates
 * @property-read \Illuminate\Database\Eloquent\Collection<Model & Templateable> $templateable
 */
interface HasTemplates
{
    /**
     * @return MorphToMany The templates associated with the model.
     */
    public function templates();

    /**
     * @return MorphMany The morph field templates associated with the model.
     */
    public function templateable();

    /**
     * @param  (Model&Template)|string|int  $template  The template to set as default, which can be a Template object, a string, or an integer.
     * @return void
     */
    public function setAsDefaultTemplate($template);

    /**
     * Get the default template.
     *
     * @return null|(Model&Template) The default template or null if not set.
     */
    public function getDefaultTemplate();

    /**
     * @return Collection<string,(Model&Template)>
     */
    public function getTemplates();
}
