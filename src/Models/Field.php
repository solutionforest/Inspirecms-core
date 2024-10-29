<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use SolutionForest\FilamentFieldGroup\Models\Field as BaseModel;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;
use SolutionForest\InspireCms\Observers\FieldObserver;

#[ObservedBy(FieldObserver::class)]
class Field extends BaseModel
{
    public function getStatePathWithGroup(): string
    {
        return implode('.', [$this->group?->name, $this->name]);
    }

    public function getFieldTypeConfigAttribute()
    {
        $fieldTypeConfig = FieldTypeHelper::getFieldTypeConfig($this->type);

        return $fieldTypeConfig?->getConfigNames() ?? [];
    }
}
