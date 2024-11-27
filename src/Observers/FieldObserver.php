<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentFieldGroup\Models\Field;

class FieldObserver
{
    /**
     * @param  Field&Model  $model
     * @return void
     */
    public function saving($model)
    {
        $model->config ??= [];
    }
}
