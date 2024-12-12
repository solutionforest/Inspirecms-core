<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Import;

interface ImportServiceInterface
{
    /**
     * Executes the pending job and mark complete.
     *
     * @param  Import&Model  $import  The job to be executed.
     * @return void
     */
    public function execute($import);

    /**
     * Builds a sample ZIP file.
     *
     * This method creates a sample ZIP file for demonstration or testing purposes.
     *
     * @return \SplFileInfo
     */
    public function buildSampleZip();
}
