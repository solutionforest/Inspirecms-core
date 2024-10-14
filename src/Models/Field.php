<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\FilamentFieldGroup\Models\Field as BaseModel;

class Field extends BaseModel
{
    public function getStatePathWithGroup(): string
    {
        return implode('.', [$this->group?->name, $this->name]);
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            $model->config ??= [];
        });
    }
}
