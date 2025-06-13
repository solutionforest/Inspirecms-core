<?php

namespace SolutionForest\InspireCms\Events\Template;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use SolutionForest\InspireCms\Models\Contracts\Template;

class UpdateContent
{
    use SerializesModels;

    /**
     * @var Template&Model
     */
    public $model;

    /**
     * @var ?string
     */
    public $theme;

    /**
     * @param  Template&Model  $model
     * @param  ?string  $theme
     */
    public function __construct($model, $theme)
    {
        $this->model = $model;
        $this->theme = $theme;
    }
}
