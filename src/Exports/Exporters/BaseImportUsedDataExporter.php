<?php

namespace SolutionForest\InspireCms\Exports\Exporters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\ImportData\Entities as ImportDataEntities;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;
use SolutionForest\InspireCms\Models\Contracts\FieldGroup;
use SolutionForest\InspireCms\Models\Contracts\Navigation;
use SolutionForest\InspireCms\Models\Contracts\Template;

abstract class BaseImportUsedDataExporter extends BaseExporter
{
    /**
     * @return string | array
     */
    protected function generateImportFileName(Model $record)
    {
        switch (true) {

            case $record instanceof DocumentType:
            case $record instanceof Content:
                return $record->slug . '.json';

            case $record instanceof FieldGroup:
                return Str::replace('_', '-', $record->name) . '.json';

            case $record instanceof Template:
                if (is_array($record->content)) {
                    $themes = array_keys($record->content);
                } else {
                    $themes = [inspirecms_templates()->getCurrentTheme()];
                }

                return collect($themes)
                    ->filter()
                    ->unique()
                    ->mapWithKeys(fn ($theme) => [$theme => $record->slug . '/' . TemplateHelper::ensureViewFileNameForTemplate($theme)])
                    ->toArray();
        }

        return $record->getKey() . '.json';
    }

    /**
     * @return array|bool|string
     */
    protected function prepareImportContentFromModel(Model $record)
    {
        switch (true) {

            case $record instanceof DocumentType:
                $array = ImportDataEntities\DocumentType::fromRecord($record)->toArray();

                return json_encode($array, JSON_PRETTY_PRINT);

            case $record instanceof FieldGroup:
                $array = ImportDataEntities\FieldGroup::fromRecord($record)->toArray();

                return json_encode($array, JSON_PRETTY_PRINT);

                // case $record instanceof Template:
                //     $themeContent = $record->content;
                //     if (! is_array($themeContent)) {
                //         $themeContent = [inspirecms_templates()->getCurrentTheme() => $themeContent];
                //     }
                //     return $themeContent;

            case $record instanceof Content:
                $array = ImportDataEntities\Content::fromRecord($record)->toArray();

                return json_encode($array, JSON_PRETTY_PRINT);

            case $record instanceof Navigation:

                $array = ImportDataEntities\Navigation::fromRecord($record)->toArray();

                return json_encode($array, JSON_PRETTY_PRINT);
        }

        return '{}';
    }

    protected function processRecordForImportUsed(Model $record, $fs, ?string $dir, array &$errors)
    {
        try {

            $filename = $this->generateImportFileName($record);

            if ($record instanceof Template && is_array($filename)) {

                foreach ($filename as $theme => $templateFilePath) {

                    $templateContent = $record->getContent($theme);

                    $path = $dir . '/' . trim($templateFilePath, '/');

                    $fs->put($path, $templateContent);
                }

            } elseif (! is_string($filename)) {
                $errors[] = [
                    'record' => $record->getKey(),
                    'model' => get_class($record),
                    'message' => 'Invalid filename',
                ];
            } else {

                $content = $this->prepareImportContentFromModel($record);

                $path = $dir . '/' . $filename;
                $fs->put($path, $content);

            }

        } catch (\Throwable $th) {
            $errors[] = [
                'record' => $record->getKey(),
                'model' => get_class($record),
                'message' => $th->getMessage(),
            ];
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildExportingQueryForImportUsed($query, $type)
    {
        switch ($type) {
            case ImportDataHelper::FOLDER_IDENTIFIER_DOCUMENTTYPE:
                return $query->with([
                    'fieldGroups',
                    'templates',
                    'allowedDocumentTypes',
                    'content',
                ]);
            case ImportDataHelper::FOLDER_IDENTIFIER_FIELDGROUP:
                return $query->with([
                    'fields',
                ]);
            case ImportDataHelper::FOLDER_IDENTIFIER_CONTENT:
                return $query->with([
                    'parent.path',
                    'sitemap',
                    'webSetting',
                    'documentType',
                    'routes.language',
                ]);
            case ImportDataHelper::FOLDER_IDENTIFIER_NAVIGATION:
                return $query->with([
                    'content.path',
                ]);
        }

        return $query;
    }

    protected static function buildProcessingDataForImportUsed(int $currentPage, int $perPage, array $errors, string $folderName)
    {
        return [
            'page' => $currentPage + 1,
            'perPage' => $perPage,
            'errors' => $errors,
            'folderName' => $folderName,
        ];
    }
}
