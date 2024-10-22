<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\FilamentFieldGroup\Models\Field as BaseModel;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;

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

    public static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            $model->config ??= [];
        });
    }
}
