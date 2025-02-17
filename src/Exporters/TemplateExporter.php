<?php

namespace SolutionForest\InspireCms\Exporters;

use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class TemplateExporter extends BaseExporter
{
    public function export()
    {
        $folderName = 'export-template-' . uniqid();
        [$fs, $fullPath, $subFolders] = $this->generateTempFolderForImport($folderName, [
            ImportDataHelper::FOLDER_IDENTIFIER_TEMPLATE,
        ]);

        $perPage = $this->export->payload['perPage'] ?? 1000;
        $page = $this->export->payload['page'] ?? 1;
        $records = $this->getRecords(perPage: $perPage, page: $page);
        $errors = [];


        foreach ($records->items() as $record) {

            $pathAndContent = $this->getFilePathAndContent($record);

            foreach ($pathAndContent as $filePath => $content) {

                try {
                    
                    $path = (Arr::first($subFolders) ?? $folderName) . '/'. $filePath;

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
        return InspireCmsConfig::getTemplateModelClass()::query()
            ->paginate(perPage: $perPage, page: $page);
    }

    private function getFilePathAndContent($record)
    {
        $slug = $record->slug;

        $themeContent = $record->content;
        if (!is_array($themeContent)) {
            $themeContent = [inspirecms_templates()->getCurrentTheme() => $themeContent];
        } 

        $result = [];

        foreach ($themeContent as $theme => $content) {

            $path = $slug . '/' . "$theme.blade.php";

            $result[$path] = $content;
        }

        return $result;
    }
}
