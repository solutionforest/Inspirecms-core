<?php

namespace SolutionForest\InspireCms\Exporters;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Helpers\FileHelper;
use SolutionForest\InspireCms\Models\Contracts\Export;

abstract class BaseExporter
{
    /**
     * @param Export & Model $export
     */
    public function __construct(
        protected $export,
    ) { }

    /**
     * @return ?string The result filename.
     */
    abstract public function export();

    public static function getLabel(): string
    {
        return str(static::class)->classBasename()->studly()->toString();
    }

    /**
     * @param  string  $folderName
     */
    protected function generateTempFolder($folderName)
    {
        $disk = $this->export->getDisk();

        // Create directory with permissions
        if (! $disk->exists($folderName)) {
            $disk->makeDirectory($folderName, 0777, true);
        }

        return [$disk, $disk->path($folderName)];
    }

    protected function generateTempFolderForImport($folderName, array $importTypes = [])
    {
        [$fs, $fullPath] =  $this->generateTempFolder($folderName);

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
        $disk = $this->export->getDisk();
        $folderFullPath = $disk->path($folderName);

        $zipPath = $folderName . '.zip';
        $zipFullPath = $disk->path($zipPath);

        FileHelper::buildZipFromFolder($folderFullPath, $zipFullPath);

        if ($deleteFolder) {
            $disk->deleteDirectory($folderName);
        }

        return $zipPath;
    }
}
