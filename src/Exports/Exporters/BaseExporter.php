<?php

namespace SolutionForest\InspireCms\Exports\Exporters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use SolutionForest\InspireCms\Exports\ExportResult;
use SolutionForest\InspireCms\Helpers\ExportDataHelper;
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
     * @return ExportResult
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

    protected function getTempFolderPath($folder)
    {
        return collect([
            ExportDataHelper::getTempDirectory(),
            $folder,
        ])->filter(fn ($item) => ! empty($item))->implode(DIRECTORY_SEPARATOR);
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function getTempDisk()
    {
        return Storage::disk(ExportDataHelper::getTempDiskDriver());
    }

    /**
     * @param  string  $folderName
     */
    protected function generateTempFolder($folderName)
    {
        $disk = $this->getTempDisk();
        $path = $this->getTempFolderPath($folderName);

        // Create directory with permissions
        if (! $disk->exists($path)) {
            $disk->makeDirectory($path, 0777, true);
        }

        return [$disk, $disk->path($path)];
    }

    protected function generateTempFolderForImport($folderName, array $importTypes = [])
    {
        [$fs, $fullPath] = $this->generateTempFolder($folderName);

        $subFolders = [];

        foreach ($importTypes as $importType) {

            $path = collect([
                ExportDataHelper::getTempDirectory(),
                $folderName,
                $importType,
            ])->filter(fn ($item) => ! empty($item))->implode(DIRECTORY_SEPARATOR);

            if (! $fs->exists($path)) {
                $fs->makeDirectory($path, 0777, true);
            }
            $subFolders[$importType] = $path;
        }

        return [$fs, $fullPath, $subFolders];
    }

    protected function zipTempFolder($folderName, bool $deleteFolder = true)
    {
        $folderDisk = $this->getTempDisk();
        $folderPath = $this->getTempFolderPath($folderName);
        $folderFullPath = $folderDisk->path($folderPath);

        $disk = $this->record->getDisk();
        $zipPath = collect([
            ExportDataHelper::getDirectory(),
            $folderName . '.zip',
        ])->filter(fn ($item) => ! empty($item))->implode(DIRECTORY_SEPARATOR);
        $zipDir = str($zipPath)->beforeLast('/')->toString();

        // Create directory with permissions
        if (filled($zipDir) && ! $disk->exists($zipDir)) {
            $disk->makeDirectory($zipDir, 0777, true);
        }

        $zipFullPath = $disk->path($zipPath);

        FileHelper::buildZipFromFolder($folderFullPath, $zipFullPath);

        if ($deleteFolder) {
            // $folderDisk->deleteDirectory($folderPath);
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
