<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentFieldGroup\Models\Field;

class FieldObserver
{
    /**
     * Handle "saving" event.
     *
     * @param  Field|Model  $model  The model instance being saving.
     * @return void
     */
    public function saving(Field | Model $model)
    {
        $model->config ??= [];
    }
}
