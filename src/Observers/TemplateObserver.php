<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Template;

class TemplateObserver
{
    /**
     * @param  Template&Model  $model
     * @return void
     */
    public function creating($model)
    {
        $model->initializeTemplate();
    }
}
