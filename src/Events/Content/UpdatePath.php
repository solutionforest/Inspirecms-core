<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Queue\SerializesModels;
use SolutionForest\InspireCms\Models\Contracts\Content;

class UpdatePath
{
    use SerializesModels;
    
    /**
     * @var Content
     */
    public $model;

    /**
     * @var null|string The slug path, optional. Null to auto-generate.
     */
    public $slugPath;
    
    /**
     * UpdatePath constructor.
     *
     * @param Content $model The content model instance.
     * @param string|null $slugPath The slug path, optional. Null to auto-generate.
     */
    public function __construct(Content $model, $slugPath = null)
    {
        $this->model = $model;
        $this->slugPath = $slugPath;
    }
}
