<?php

namespace SolutionForest\InspireCms\Exporters;

use SolutionForest\InspireCms\Helpers\FileHelper;
use SolutionForest\InspireCms\ImportData\Entities\DocumentType;
use SolutionForest\InspireCms\InspireCmsConfig;

/**
 * @todo Handle if have large data in db
 */
class DocumentTypeExporter extends BaseExporter
{
    public function export()
    {
        $folderName = 'export-document-types' . uniqid();
        [$fs, $fullPath] = $this->generateTempFolder($folderName);

        $records = $this->getRecords();

        $list = collect($records)
            ->mapWithKeys(fn ($record) => [
                $this->getFileNameForRecord($record) => $this->convertToExportContent($record),
            ])
            ->toArray();

        foreach ($list as $filename => $content) {
            $fs->put("{$folderName}/{$filename}", $content);
        }

        $zipPath = $folderName . '.zip';
        $zipFullPath = $fullPath . '.zip';
        FileHelper::buildZipFromFolder($fullPath, $zipFullPath);

        // remove temp folder
        $fs->deleteDirectory($folderName);

        return $zipPath;
    }

    protected function getRecords()
    {
        return InspireCmsConfig::getDocumentTypeModelClass()::query()
            ->with(['fieldGroups', 'templates', 'rejectedDocumentTypes'])
            ->get();
    }

    protected function convertToExportContent($record)
    {
        $array = DocumentType::fromRecord($record)->toExportArray();

        return json_encode($array, JSON_PRETTY_PRINT);
    }

    protected function getFileNameForRecord($record)
    {
        return $record->slug . '.json';
    }
}
