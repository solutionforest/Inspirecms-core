<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Queue\SerializesModels;

class DispatchIndexModel
{
    use SerializesModels;

    /**
     * @var string
     */
    public $model;

    public function __construct($model)
    {
        $this->model = $model;
    }
}
