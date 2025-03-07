<?php

namespace SolutionForest\InspireCms\Exports\Exporters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Exports\ExportResult;
use SolutionForest\InspireCms\Helpers\FileHelper;
use SolutionForest\InspireCms\ImportData\Entities as ImportDataEntities;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;
use SolutionForest\InspireCms\Models\Contracts\Export;
use SolutionForest\InspireCms\Models\Contracts\FieldGroup;
use SolutionForest\InspireCms\Models\Contracts\Template;

abstract class BaseExporter
{
    /**
     * @param  Export & Model  $record
     */
    public function __construct(
        protected $record,
    ) {}

    /**
     * @return \SolutionForest\InspireCms\Exports\ExportResult
     */
    abstract public function export();

    /**
     * Get the form fields for the arguments of this exporter.
     *
     * @return array An array of form fields for the arguments.
     */
    abstract public static function getArgsFormFields(): array;

    public static function getLabel(): string
    {
        return str(static::class)->classBasename()->studly()->toString();
    }

    /**
     * @param  string  $folderName
     */
    protected function generateTempFolder($folderName)
    {
        $disk = $this->record->getDisk();

        // Create directory with permissions
        if (! $disk->exists($folderName)) {
            $disk->makeDirectory($folderName, 0777, true);
        }

        return [$disk, $disk->path($folderName)];
    }

    protected function generateTempFolderForImport($folderName, array $importTypes = [])
    {
        [$fs, $fullPath] = $this->generateTempFolder($folderName);

        $subFolders = [];
        foreach ($importTypes as $importType) {
            $folderPath = $folderName . '/' . $importType;
            if (! $fs->exists($folderPath)) {
                $fs->makeDirectory($folderPath, 0777, true);
            }
            $subFolders[$importType] = $folderPath;
        }

        return [$fs, $fullPath, $subFolders];
    }

    protected function zipTempFolder($folderName, bool $deleteFolder = true)
    {
        $disk = $this->record->getDisk();
        $folderFullPath = $disk->path($folderName);

        $zipPath = $folderName . '.zip';
        $zipFullPath = $disk->path($zipPath);

        FileHelper::buildZipFromFolder($folderFullPath, $zipFullPath);

        if ($deleteFolder) {
            $disk->deleteDirectory($folderName);
        }

        return $zipPath;
    }

    protected function ensureTempFolderForExport(string $folderPrefix, array $subFolders = [])
    {
        $processingData = $this->record->getProcessingMessages();

        if (! empty($processingData['folderName'])) {
            $folderName = $processingData['folderName'];
        } else {
            $folderName = $folderPrefix . '-' . uniqid();
        }

        $tmpFolderData = [$fs, $fullPath, $subFolders] = $this->generateTempFolderForImport($folderName, $subFolders);

        return array_merge([$folderName], $tmpFolderData);
    }

    protected function generateImportFileName(Model $record)
    {
        switch (true) {
            case $record instanceof DocumentType:
                return $record->slug . '.json';

            case $record instanceof FieldGroup:
                return Str::replace('_', '-', $record->name) . '.json';

                // case $record instanceof Template:
                //     if (is_array($record->content)) {
                //         $themes = array_keys($record->content);
                //     } else {
                //         $themes = [inspirecms_templates()->getCurrentTheme()];
                //     }
                //     return collect($themes)
                //         ->filter()
                //         ->unique()
                //         ->map(fn ($theme) => $record->slug . '/' . "$theme.blade.php")
                //         ->toArray();
        }

        return $record->getKey() . '.json';
    }

    protected function prepareImportContentFromModel(Model $record)
    {
        switch (true) {
            case $record instanceof DocumentType:
                $array = ImportDataEntities\DocumentType::fromRecord($record)->toExportArray();

                return json_encode($array, JSON_PRETTY_PRINT);

            case $record instanceof FieldGroup:
                $array = ImportDataEntities\FieldGroup::fromRecord($record)->toExportArray();

                return json_encode($array, JSON_PRETTY_PRINT);

                // case $record instanceof Template:
                //     $themeContent = $record->content;
                //     if (! is_array($themeContent)) {
                //         $themeContent = [inspirecms_templates()->getCurrentTheme() => $themeContent];
                //     }
                //     return $themeContent;
        }

        return '';
    }

    protected static function isProcessCompleted(int $currentPage, int $totalPages, array $processingData)
    {
        return $currentPage >= $totalPages;
    }

    protected static function buildProcessingData(int $currentPage, int $perPage, array $errors, string $folderName)
    {
        return [
            'page' => $currentPage + 1,
            'perPage' => $perPage,
            'errors' => $errors,
            'folderName' => $folderName,
        ];
    }

    protected function handleExportCompletion(string $folderName, $processingErrors)
    {
        $zippedFile = $this->zipTempFolder($folderName);

        $completedMessage = null;
        if (! empty($processingErrors)) {
            $completedMessage = [
                'processing_errors' => $processingErrors,
            ];
        }

        return ExportResult::completed($zippedFile, $completedMessage);
    }

    protected function processRecordForImportUsed(Model $record, $fs, ?string $dir, array &$errors)
    {
        try {
            $content = $this->prepareImportContentFromModel($record);
            $filename = $this->generateImportFileName($record);
            $path = $dir . '/' . $filename;

            $fs->put($path, $content);

        } catch (\Throwable $th) {
            $errors[] = [
                'record' => $record->getKey(),
                'message' => $th->getMessage(),
            ];
        }
    }
}
