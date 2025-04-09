<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Export;

interface ExportServiceInterface
{
    /**
     * @param  Export & Model  $export
     * @return void
     */
    public function execute($export);
}
