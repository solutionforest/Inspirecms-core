<?php

namespace SolutionForest\InspireCms\Events\Export;

use Illuminate\Queue\SerializesModels;

class Completed
{
    use SerializesModels;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\Export&\Illuminate\Database\Eloquent\Model
     */
    public $export;

    /**
     * @param  \SolutionForest\InspireCms\Models\Contracts\Export&\Illuminate\Database\Eloquent\Model  $export
     */
    public function __construct($export)
    {
        $this->export = $export;
    }
}
