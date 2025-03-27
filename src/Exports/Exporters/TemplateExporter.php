<?php

namespace SolutionForest\InspireCms\Exports\Exporters;

use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Exports\ExportResult;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class TemplateExporter extends BaseImportUsedDataExporter
{
    public static function getArgsFormFields(): array
    {
        return [];
    }

    public function export()
    {
        [$folderName, $fs, $fullPath, $subFolders] = $this->ensureTempFolderForExport('export-templates', [
            ImportDataHelper::FOLDER_IDENTIFIER_TEMPLATE,
        ]);

        [$records, $perPage, $page] = $this->getTemplateRecords();
        $errors = [];

        foreach ($records->items() as $record) {

            $this->processRecordForImportUsed(
                $record,
                $fs,
                (Arr::first($subFolders) ?? $folderName),
                $errors,
            );
        }

        $processingErrors = array_merge(
            $this->record->getProcessingMessages()['errors'] ?? [],
            $errors,
        );

        if ($page >= $records->lastPage()) {
            return $this->handleExportCompletion($folderName, $processingErrors);
        }

        return ExportResult::paused(
            $this->buildProcessingDataForImportUsed($page, $perPage, $processingErrors, $folderName),
        );
    }

    private function getTemplateRecords()
    {
        $processingData = $this->record->getProcessingMessages();
        $perPage = $processingData['perPage'] ?? 100;
        $page = $processingData['page'] ?? 1;

        $query = static::getModel()::query();

        $records = $query->paginate(perPage: $perPage, page: $page);

        return [$records, $perPage, $page];
    }

    private static function getModel()
    {
        return InspireCmsConfig::getTemplateModelClass();
    }
}
