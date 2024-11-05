<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Queue\SerializesModels;

class GenerateSitemap
{
    use SerializesModels;

    /**
     * @var class-string
     */
    public $model;

    /**
     * @var string|int
     */
    public $modelKey;

    /**
     * @var string
     */
    public $triggerEvent;

    public function __construct($model, $modelKey, $triggerEvent)
    {
        $this->model = $model;
        $this->modelKey = $modelKey;
        $this->triggerEvent = $triggerEvent;
    }
}
