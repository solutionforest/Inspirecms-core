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
     * Generates and returns the HTML representation of the file structure.
     *
     * @return Htmlable The HTML representation of the file structure.
     */
    public static function getFileStructureHtml();
}
