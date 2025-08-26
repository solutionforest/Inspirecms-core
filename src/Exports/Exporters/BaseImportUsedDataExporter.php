<?php

namespace SolutionForest\InspireCms\Exports\Exporters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Helpers\FileHelper;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\ImportData\Entities\Content as ImportEntitiesContent;
use SolutionForest\InspireCms\ImportData\Entities\DocumentType as ImportEntitiesDocumentType;
use SolutionForest\InspireCms\ImportData\Entities\FieldGroup as ImportEntitiesFieldGroup;
use SolutionForest\InspireCms\ImportData\Entities\Language as ImportEntitiesLanguage;
use SolutionForest\InspireCms\ImportData\Entities\MediaAsset as ImportEntitiesMediaAsset;
use SolutionForest\InspireCms\ImportData\Entities\Navigation as ImportEntitiesNavigation;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;
use SolutionForest\InspireCms\Models\Contracts\FieldGroup;
use SolutionForest\InspireCms\Models\Contracts\Language;
use SolutionForest\InspireCms\Models\Contracts\Navigation;
use SolutionForest\InspireCms\Models\Contracts\Template;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

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

            case $record instanceof Language:
                return $record->code . '.json';

            case $record instanceof FieldGroup:
                return Str::replace('_', '-', $record->name) . '.json';

            case $record instanceof MediaAsset:
                return $record->id . '.json';

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
                $array = ImportEntitiesDocumentType::fromRecord($record)->toArray();

                return json_encode($array, JSON_PRETTY_PRINT);

            case $record instanceof FieldGroup:
                $array = ImportEntitiesFieldGroup::fromRecord($record)->toArray();

                return json_encode($array, JSON_PRETTY_PRINT);

            case $record instanceof Content:
                $array = ImportEntitiesContent::fromRecord($record)->toArray();

                return json_encode($array, JSON_PRETTY_PRINT);

            case $record instanceof Navigation:

                $array = ImportEntitiesNavigation::fromRecord($record)->toArray();

                return json_encode($array, JSON_PRETTY_PRINT);

            case $record instanceof Language:
                $array = ImportEntitiesLanguage::fromRecord($record)->toArray();

                return json_encode($array, JSON_PRETTY_PRINT);

            case $record instanceof MediaAsset:
                $array = ImportEntitiesMediaAsset::fromRecord($record)->toArray();

                return json_encode($array, JSON_PRETTY_PRINT);
        }

        return '{}';
    }

    protected function processRecordForImportUsed(Model $record, $fs, ?string $dir, array &$errors)
    {
        try {

            $filename = $this->generateImportFileName($record);

            if ($record instanceof Template && is_array(value: $filename)) {

                foreach ($filename as $theme => $templateFilePath) {

                    $templateContent = $record->getContent($theme);

                    $path = $dir . '/' . trim($templateFilePath, '/');

                    $fs->put($path, $templateContent);
                }

            } elseif ($record instanceof MediaAsset) {

                // Export the JSON metadata file
                $content = $this->prepareImportContentFromModel($record);
                $path = $dir . '/' . trim($filename, '/');
                $fs->put($path, $content);

                // Handle media files
                try {
                    $dto = ImportEntitiesMediaAsset::fromArray(json_decode($content, true));
                    foreach ($dto->media_files as $item) {

                        $paths = $item['__exported_file_path'];

                        if (!is_array($paths) || empty($paths)) {
                            continue;
                        }

                        foreach ($paths as $key => $value) {
                            try {

                                $tmpMediaFilePath = collect([$dir, $value])->map(fn ($path) => trim($path, '/'))->implode('/');

                                // Ensure the directory exists
                                if (! $fs->exists(dirname($tmpMediaFilePath))) {

                                    $fs->makeDirectory(dirname($tmpMediaFilePath), 0777, true);
                                }

                                $mediaFileContent = ($key == '__real__' ? Storage::disk($record->disk) : Storage::disk($record->conversions_disk))->get($value);

                                $fs->put($tmpMediaFilePath, $mediaFileContent);

                            } catch (\Throwable $th) {
                                // Skip error, handle next file
                            }
                        }
                    }
                } catch (\Throwable $th) {
                    $errors[] = [
                        'record' => $record->getKey(),
                        'model' => get_class($record),
                        'message' => $th->getMessage(),
                    ];
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

            case ImportDataHelper::FOLDER_IDENTIFIER_TEMPLATE:
                return $query;

            case ImportDataHelper::FOLDER_IDENTIFIER_MEDIAASSET:
                return $query->with([
                    // 'parent',
                    'nestableTree',
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
