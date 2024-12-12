<?php

namespace SolutionForest\InspireCms\Events\Import;

use Illuminate\Queue\SerializesModels;

class Completed
{
    use SerializesModels;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\Import&\Illuminate\Database\Eloquent\Model
     */
    public $import;

    /**
     * @param  \SolutionForest\InspireCms\Models\Contracts\Import&\Illuminate\Database\Eloquent\Model  $import
     */
    public function __construct($import)
    {
        $this->import = $import;
    }
}
