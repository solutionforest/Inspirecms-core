<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Events\CreateTemplate;
use SolutionForest\InspireCms\Models\Contracts\Template;

class TemplateObserver
{
    /**
     * Handle "creating" event.
     *
     * @param  Template|Model  $model  The model instance being creating.
     * @return void
     */
    public function creating(Template | Model $model)
    {
        $model->path = $model->performTemplatePath();

        $model->createTemplateFile();

        event(new CreateTemplate($model));
    }

    /**
     * Handle "saving" event.
     *
     * @param  Template|Model  $model  The model instance being saving.
     * @return void
     */
    public function saving(Template | Model $model)
    {
        $model->path = $model->performTemplatePath();
    }
}
