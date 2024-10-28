<?php

namespace SolutionForest\InspireCms\Events\Indexes;

use Illuminate\Queue\SerializesModels;

class IndexingModel
{
    use SerializesModels;

    /**
     * @var \Illuminate\Database\Eloquent\Mode $model The model associated with the index.
     */
    public $model;

    /**
     * @var array $data The data associated with the index model.
     */
    public $data;

    public function __construct($model, $data)
    {
        $this->model = $model;
        $this->data = $data;
    }
}
