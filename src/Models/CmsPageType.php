<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\FilamentFieldGroup\Models\FieldGroup;
use SolutionForest\InspireCms\Models\Polymorphic\ModelHasFieldGroup;

class CmsPageType extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_root_level' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('inspirecms-core.models.page_type.table_name'));
    }

    public function fieldGroups(): MorphToMany
    {
        return $this->morphToMany(config('filament-field-group.models.field_group', FieldGroup::class), 'model', config('inspirecms-core.models.model_has_field_groups.table_name', 'cms_model_has_field_groups'));
    }

    public function morphFieldGroups(): MorphMany
    {
        return $this->morphMany(config('inspirecms-core.models.model_has_field_groups.fqcn', ModelHasFieldGroup::class), 'model');
    }
}
