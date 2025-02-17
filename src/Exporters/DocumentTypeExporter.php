<?php

namespace SolutionForest\InspireCms\Exporters;

use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\ImportData\Entities\DocumentType;
use SolutionForest\InspireCms\InspireCmsConfig;

class DocumentTypeExporter extends BaseExporter
{
    public function export()
    {
        $folderName = 'export-document-types-' . uniqid();
        [$fs, $fullPath, $subFolders] = $this->generateTempFolderForImport($folderName, [
            ImportDataHelper::FOLDER_IDENTIFIER_DOCUMENTTYPE,
        ]);

        $perPage = $this->export->payload['perPage'] ?? 1000;
        $page = $this->export->payload['page'] ?? 1;
        $records = $this->getRecords(perPage: $perPage, page: $page);
        $errors = [];

        foreach ($records->items() as $record) {

            try {

                $filename = $this->getFileNameForRecord($record);
                $content = $this->convertToExportContent($record);
                $path = (Arr::first($subFolders) ?? $folderName) . '/'. $filename;

                $fs->put($path, $content);

            } catch (\Throwable $th) {
                $errors[] = [
                    'record' => $record->getKey(),
                    'message' => $th->getMessage(),
                ];
            }
        }

        // pause
        if ($page < $records->lastPage()) {
            $payload = [
                'page' => $page + 1,
                'perPage' => $perPage,
                'errors' => array_merge($this->export->payload['errors'] ?? [], $errors),
            ];
            $this->export->markAsPaused($payload);
            return null;
        }

        return $this->zipTempFolder($folderName);
    }

    private function getRecords($perPage, $page)
    {
        return InspireCmsConfig::getDocumentTypeModelClass()::query()
            ->with(['fieldGroups', 'templates', 'rejectedDocumentTypes'])
            ->paginate(perPage: $perPage, page: $page);
    }

    private function convertToExportContent($record)
    {
        $array = DocumentType::fromRecord($record)->toExportArray();

        return json_encode($array, JSON_PRETTY_PRINT);
    }

    private function getFileNameForRecord($record)
    {
        return $record->slug . '.json';
    }
}
