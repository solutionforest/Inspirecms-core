<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Queue\SerializesModels;
use SolutionForest\InspireCms\Models\Contracts\Base\HasContentVersions;

class DispatchContentVersion
{
    use SerializesModels;
    
    /**
     * @var HasContentVersions
     */
    public $model;


    public function __construct(HasContentVersions $model)
    {
        $this->model = $model;
    }
}
