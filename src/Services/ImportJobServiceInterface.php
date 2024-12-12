<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\ImportJob;

interface ImportJobServiceInterface
{
    /**
     * Executes the pending job and mark complete.
     *
     * @param  ImportJob&Model  $job  The job to be executed.
     * @return void
     */
    public function execute($job);
    
    /**
     * Builds a sample ZIP file.
     *
     * This method creates a sample ZIP file for demonstration or testing purposes.
     *
     * @return \SplFileInfo
     */
    public function buildSampleZip();

    
    /**
     * Get the sample file structure for import jobs.
     *
     * This method returns an array representing the structure of a sample file
     * that can be used for import jobs. The structure typically includes the
     * necessary headers and format required for a successful import.
     *
     * @return array The sample file structure.
     */
    public static function getSampleFileStructure();
}
