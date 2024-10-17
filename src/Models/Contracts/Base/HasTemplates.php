<?php

namespace SolutionForest\InspireCms\Models\Contracts\Base;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\InspireCms\Models\Contracts\Template;

interface HasTemplates
{
    /**
     * Get the templates associated with the document type.
     *
     * @return MorphToMany The templates associated with the document type.
     */
    public function templates(): MorphToMany;

    /**
     * Get the morph field templates associated with the document type.
     *
     * @return MorphMany The morph field templates associated with the document type.
     */
    public function templateable(): MorphMany;

    /**
     * Set the specified template as the default for the document type.
     *
     * @param  Template|string|int  $template  The template to set as default, which can be a Template object, a string, or an integer.
     */
    public function setAsDefaultTemplate(Template | string | int $template): void;

    public function getDefaultTemplate(): ?Template;
}
