<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Events\CreateTemplate;
use SolutionForest\InspireCms\Models\Contracts\Template;

class TemplateObserver
{
    /**
     * @param  Template&Model  $model
     * @return void
     */
    public function creating($model)
    {
        $model->path = $model->retrieveTemplatePath();

        $model->createTemplateFile();

        event(new CreateTemplate($model));
    }

    /**
     * @param  Template&Model  $model
     * @return void
     */
    public function saving($model)
    {
        $model->path = $model->retrieveTemplatePath();
    }
}
