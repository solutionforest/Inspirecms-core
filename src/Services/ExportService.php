<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Exporters\BaseExporter;
use SolutionForest\InspireCms\Models\Contracts\Export;

class ExportService implements ExportServiceInterface
{
    /**
     * @param  Export & Model  $export
     * @return void
     */
    public function execute($export)
    {
        /**
         * @var BaseExporter
         */
        $exporter = app($export->exporter, ['export' => $export]);

        try {

            $filename = $exporter->export();

            $export->markAsCompleted($filename);

        } catch (\Throwable $th) {

            $export->markAsFailed($th);

        }
    }
}
