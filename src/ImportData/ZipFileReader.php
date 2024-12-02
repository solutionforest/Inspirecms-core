<?php

namespace SolutionForest\InspireCms\ImportData;

use Illuminate\Support\Facades\Storage;
use SolutionForest\InspireCms\Helpers\FileHelper;

class ZipFileReader
{
    /**
     * Reads the contents of a ZIP file from the specified file path.
     *
     * @param  string  $zipFilePath  The path to the ZIP file to be read.
     * @return array{0:\Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter,1:string}|null The filesystem instance, the relative path to the folder, or null if the file does not exist or is not a ZIP file.
     */
    public function readFromPath(string $zipFilePath)
    {
        if (! file_exists($zipFilePath)) {
            return null;
        }

        // Return if not a zip file
        if (pathinfo($zipFilePath, PATHINFO_EXTENSION) !== 'zip') {
            return null;
        }

        [$fs, $fullExtractTo, $extractTo] = $this->generateFolderForExtraction(uniqid());

        logger()->debug('Extracting ZIP file', [
            'zipFilePath' => $zipFilePath,
            'extractTo' => $extractTo,
            'extractTofull' => $fullExtractTo,
        ]);

        FileHelper::unzipFile($zipFilePath, $fullExtractTo);

        return [$fs, $extractTo];
    }

    public function getTempDiskDriver(): string
    {
        return 'local';
        return strval(config('inspirecms.imports.temp_disk', 'local'));
    }

    /**
     * Get the temporary disk filesystem instance.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter
     */
    public function getTempDisk()
    {
        return Storage::disk($this->getTempDiskDriver());
    }

    /**
     * Generates a folder for extraction.
     *
     * @param  string  $folderName  The name of the folder to be created for extraction.
     * @return array{\Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter,string,string} The filesystem instance, the full path to the folder, and the relative path to the folder.
     */
    protected function generateFolderForExtraction($folderName)
    {
        $disk = $this->getTempDisk();

        $tempDir = strval(config('inspirecms.imports.temp_directory', 'temp/imports'));

        $path = $tempDir . DIRECTORY_SEPARATOR . $folderName;

        // Create directory with permissions
        if (! $disk->exists($path)) {
            $disk->makeDirectory($path, 0755, true);
        }

        return [
            $disk,
            $disk->path($path),
            $path,
        ];
    }
}
