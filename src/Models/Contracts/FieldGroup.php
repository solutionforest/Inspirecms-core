<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\FilamentFieldGroup\Models\Contracts\FieldGroup as BaseContract;

interface FieldGroup extends BaseContract
{
    /**
     * Get all of the docuemnt types that are assigned this field group.
     */
    public function documentTypes(): MorphToMany;

    public function groupabled(): HasMany;
}
