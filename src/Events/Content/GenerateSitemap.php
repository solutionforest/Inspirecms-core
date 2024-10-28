<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Queue\SerializesModels;

class GenerateSitemap
{
    use SerializesModels;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * @var string
     */
    public $triggerEvent;

    public function __construct($model, $triggerEvent)
    {
        $this->model = $model;
        $this->triggerEvent = $triggerEvent;
    }
}
