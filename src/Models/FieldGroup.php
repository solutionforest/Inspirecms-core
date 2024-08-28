<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\FilamentFieldGroup\Models\FieldGroup as BaseModel;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class FieldGroup extends BaseModel
{
    /**
     * Get all of the docuemnt types that are assigned this field group.
     */
    public function documentTypes(): MorphToMany
    {
        return $this->morphedByMany(InspireCmsConfig::getDocumentTypeModelClass(), 'model', InspireCmsConfig::getComponentFieldGroupTableName());
    }

    public static function booting()
    {
        // Do before parent::boot()
        static::deleting(function (self $group) {
            // Check if the group is associated with any document types
            if ($group->documentTypes()->exists()) {
                throw new \Exception('Cannot delete this field group because it is in use.');
            }
        });
    }
}
