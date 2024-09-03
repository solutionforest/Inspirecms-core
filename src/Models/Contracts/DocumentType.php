<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface DocumentType
{
    /**
     * Get the field groups associated with the document type.
     *
     * This method should return a MorphToMany relationship
     * representing the field groups linked to the document type.
     *
     * @return MorphToMany The field groups associated with the document type.
     */
    public function fieldGroups(): MorphToMany;

    /**
     * Get the morph field groups associated with the document type.
     *
     * This method should return a MorphMany relationship
     * representing the morph field groups linked to the document type.
     *
     * @return MorphMany The morph field groups associated with the document type.
     */
    public function morphFieldGroups(): MorphMany;
    
    /**
     * Get the contents associated with the document type.
     *
     * This method should return a HasMany relationship
     * representing the contents linked to the document type.
     *
     * @return HasMany The contents associated with the document type.
     */
    public function contents(): HasMany;
}
