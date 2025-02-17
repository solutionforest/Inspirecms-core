<?php

namespace SolutionForest\InspireCms\Services;

interface ExportServiceInterface
{
    /**
     * @param  Export & Model  $export
     * @return void
     */
    public function execute($export);
}
