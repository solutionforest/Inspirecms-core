<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Exports\Exporters\BaseExporter;
use SolutionForest\InspireCms\Models\Contracts\Export;
use Throwable;

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
        $exporter = app($export->exporter, ['record' => $export]);

        try {

            $result = $exporter->export();

            if ($result->status->isPaused()) {
                $export->markAsPaused($result->message);
            } elseif ($result->status->isFailed()) {
                $export->markAsFailed($result->message);
            } elseif ($result->status->isCompleted()) {
                $export->markAsCompleted($result->filename, $result->message);
            }

        } catch (Throwable $th) {
            $export->markAsFailed($th);

        }
    }
}
