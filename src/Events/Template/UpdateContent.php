<?php

namespace SolutionForest\InspireCms\Events\Template;

use Illuminate\Queue\SerializesModels;

class UpdateContent
{
    use SerializesModels;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\Template&\Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * @var ?string
     */
    public $theme;

    /**
     * @param  \SolutionForest\InspireCms\Models\Contracts\Template&\Illuminate\Database\Eloquent\Model  $model
     * @param  ?string  $theme
     */
    public function __construct($model, $theme)
    {
        $this->model = $model;
        $this->theme = $theme;
    }
}
