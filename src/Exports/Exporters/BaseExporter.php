<?php

namespace SolutionForest\InspireCms\Exports\Exporters;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Exports\ExportResult;
use SolutionForest\InspireCms\Helpers\FileHelper;
use SolutionForest\InspireCms\Models\Contracts\Export;

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
        return str(static::class)->classBasename()->snake()->replace('_', ' ')->apa()->toString();
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

    protected static function isProcessCompleted(int $currentPage, int $totalPages, array $processingData)
    {
        return $currentPage >= $totalPages;
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
}
