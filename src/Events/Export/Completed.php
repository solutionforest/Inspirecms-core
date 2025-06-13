<?php

namespace SolutionForest\InspireCms\Events\Export;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use SolutionForest\InspireCms\Models\Contracts\Export;

class Completed
{
    use SerializesModels;

    /**
     * @var Export&Model
     */
    public $export;

    /**
     * @param  Export&Model  $export
     */
    public function __construct($export)
    {
        $this->export = $export;
    }
}
