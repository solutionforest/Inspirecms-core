<?php

namespace SolutionForest\InspireCms\Exporters;

use Illuminate\Support\Str;
use SolutionForest\InspireCms\Helpers\FileHelper;
use SolutionForest\InspireCms\ImportData\Entities\FieldGroup;
use SolutionForest\InspireCms\InspireCmsConfig;

class FieldGroupExporter extends BaseExporter
{
    public function export()
    {
        $folderName = 'export-fields' . uniqid();
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
        return InspireCmsConfig::getFieldGroupModelClass()::query()
            ->with(['fields'])
            ->get();
    }

    protected function convertToExportContent($record)
    {
        $array = FieldGroup::fromRecord($record)->toExportArray();

        return json_encode($array, JSON_PRETTY_PRINT);
    }

    protected function getFileNameForRecord($record)
    {
        return Str::replace('_', '-', $record->name) . '.json';
    }
}
