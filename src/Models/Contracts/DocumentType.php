<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface DocumentType
{
    /**
     * Get the field groups associated with the document type.
     *
     * @return MorphToMany The field groups associated with the document type.
     */
    public function fieldGroups(): MorphToMany;

    /**
     * Get the morph field groups associated with the document type.
     *
     * @return MorphMany The morph field groups associated with the document type.
     */
    public function fieldGroupables(): MorphMany;

    /**
     * Get the contents associated with the document type.
     *
     * @return HasMany The contents associated with the document type.
     */
    public function contents(): HasMany;

    /**
     * Get the templates associated with the document type.
     *
     * @return MorphToMany The templates associated with the document type.
     */
    public function templates(): MorphToMany;

    /**
     * Get the default template associated with the document type.
     *
     * @return MorphOne The default template associated with the document type.
     */
    public function defaultTemplate(): MorphOne;

    /**
     * Get the morph field templates associated with the document type.
     *
     * @return MorphMany The morph field templates associated with the document type.
     */
    public function templatable(): MorphMany;

    /**
     * Set the specified template as the default for the document type.
     *
     * @param  Template  $template  The template to set as default.
     */
    public function setAsDefaultTemplate(Template $template): void;
}
