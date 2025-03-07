<?php

namespace SolutionForest\InspireCms\Exports\Exporters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Exports\ExportResult;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class TemplateExporter extends BaseExporter
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
            $this->buildProcessingData($page, $perPage, $processingErrors, $folderName),
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

    private function getFilePathAndContent($record)
    {
        $slug = $record->slug;

        $themeContent = $record->content;
        if (! is_array($themeContent)) {
            $themeContent = [inspirecms_templates()->getCurrentTheme() => $themeContent];
        }

        $result = [];

        foreach ($themeContent as $theme => $content) {

            $path = $slug . '/' . "$theme.blade.php";

            $result[$path] = $content;
        }

        return $result;
    }

    protected function processRecordForImportUsed(Model $record, $fs, ?string $dir, array &$errors)
    {
        $pathAndContent = $this->getFilePathAndContent($record);

        foreach ($pathAndContent as $filePath => $content) {

            try {

                $path = $dir . '/' . $filePath;

                $fs->put($path, $content);

            } catch (\Throwable $th) {
                $errors[] = [
                    'record' => $record->getKey(),
                    'path' => $filePath,
                    'message' => $th->getMessage(),
                ];
            }
        }
    }
}
