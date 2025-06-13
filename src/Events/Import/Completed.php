<?php

namespace SolutionForest\InspireCms\Events\Import;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use SolutionForest\InspireCms\Models\Contracts\Import;

class Completed
{
    use SerializesModels;

    /**
     * @var Import&Model
     */
    public $import;

    /**
     * @param  Import&Model  $import
     */
    public function __construct($import)
    {
        $this->import = $import;
    }
}
