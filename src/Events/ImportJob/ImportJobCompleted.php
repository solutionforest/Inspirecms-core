<?php

namespace SolutionForest\InspireCms\Events\ImportJob;

use Illuminate\Queue\SerializesModels;

class ImportJobCompleted
{
    use SerializesModels;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\ImportJob&\Illuminate\Database\Eloquent\Model
     */
    public $job;

    /**
     * @param  \SolutionForest\InspireCms\Models\Contracts\ImportJob&\Illuminate\Database\Eloquent\Model  $job
     */
    public function __construct($job)
    {
        $this->job = $job;
    }
}
