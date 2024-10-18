<?php

namespace SolutionForest\InspireCms\Base\Services;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModelSerivce
{
    /**
     * @var Model
     */
    protected $model;

    public function __construct(
        private string $modelClass,
    )
    {
        $this->model = new $this->modelClass;
    }

    /**
     * Begin querying the model.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getQuery()
    {
        return $this->model->query();
    }
}
