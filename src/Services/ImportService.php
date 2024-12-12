<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Helpers\FileHelper;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\Helpers\ThrowableHelper;
use SolutionForest\InspireCms\ImportData\Entities;
use SolutionForest\InspireCms\ImportData\ZipFileReader;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Services\ImportServiceInterface;

class ImportService implements ImportServiceInterface
{
    public function __construct(
        protected ImportDataServiceInterface $importDataService,
        protected ZipFileReader $zipFileReader,
    ) {}

    /** {@inheritDoc} */
    public function execute($import)
    {
        $extractorFs = null;
        $extractedFolderPath = null;

        $message = [];

        try {

            // Ensure the import data service is reset
            $this->importDataService->reset();

            [$fs, $filePath] = $import->getStorageAndFilePath();

            [$extractorFs, $extractedFolderPath] = $this->zipFileReader->extractFromZip($fs->path($filePath));

            if (is_null($extractedFolderPath)) {
                throw new \Exception('The provided file is not a ZIP file.');
            }

            $folderPaths = $extractorFs->directories($extractedFolderPath);

            foreach ($folderPaths as $folderPath) {

                $folderName = static::getFolderNameFromExtractedPath($folderPath);

                // Fixed folder structure
                if (! in_array($folderName, ImportDataHelper::FOLDER_STRUCTURE)) {
                    continue;
                }

                if (in_array($folderName, ImportDataHelper::FOLDER_HAS_VIEWS)) {

                    $failedForViews = $this->duplicateViewFiles(fs: $extractorFs, folderPath: $folderPath, forType: $folderName);
                    if (! empty($failedForViews)) {
                        $message[] = [
                            'message' => "Failed to duplicate view files for {$folderName}.",
                            'files' => $failedForViews,
                        ];
                    }

                }

                $failedForImportData = $this->importDataForType($extractorFs, $folderPath, $folderName);
                if (! empty($failedForImportData)) {
                    $message[] = [
                        'message' => "Failed to import data for {$folderName}.",
                        'files' => $failedForImportData,
                    ];
                }
            }

            $this->importDataService->run();

            $importErrors = $this->importDataService->getErrors();
            if (! empty($importErrors)) {
                $message[] = [
                    'message' => 'Some errors occurred during the import process.',
                    'errors' => $importErrors,
                ];
            }

            $import->markAsCompleted(
                empty($message) ? null : $message
            );

        } catch (\Throwable $th) {

            $message[] = [
                'exMessage' => $th->getMessage(),
                'exTrace' => ThrowableHelper::getTraceAsString($th, 5),
            ];

            $import->markAsFailed($message);

        } finally {
            // Delete the extracted folder
            if ($extractorFs != null && $extractedFolderPath != null && $extractorFs->exists($extractedFolderPath)) {
                $extractorFs->deleteDirectory($extractedFolderPath);
            }
        }
    }

    /** {@inheritDoc} */
    public function buildSampleZip()
    {
        $sampleData = ImportDataHelper::generateSampleData();

        [$fs, $fullPath, $path] = $this->zipFileReader->generateFolderForExtraction(uniqid());

        foreach ($sampleData as $folder => $files) {
                
            $folderPath = $path . DIRECTORY_SEPARATOR . $folder;

            $fs->makeDirectory($folderPath);

            foreach ($files as $filename => $content) {
                $fs->put($folderPath . DIRECTORY_SEPARATOR . $filename, $content);
            }
        }

        $zipPath = $path . '.zip';
        $zipFullPath = $fs->path($zipPath);

        FileHelper::buildZipFromFolder($fullPath, $zipFullPath);

        // Delete the folder
        $fs->deleteDirectory($path);
        
        return new \SplFileInfo($zipFullPath);
    }

    /**
     * @param  \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter  $fs  The filesystem instance.
     * @param  string  $folderPath  The path to the folder containing the view files to duplicate.
     * @param  string  $forType
     * @return array|null Error message for the file or null
     */
    protected function importDataForType($fs, $folderPath, $forType)
    {
        $neededTypes = collect(array_flip(ImportDataHelper::FOLDER_STRUCTURE))->except(ImportDataHelper::FOLDER_IDENTIFIER_VIEW)->keys()->all();

        if (! in_array($forType, $neededTypes)) {
            return;
        }

        return static::fileProcessingWithCallback(fs: $fs, folderPath: $folderPath, callback: function ($fs, $folderPath, $file) use ($forType) {

            $slug = Str::before(basename($file), '.');

            // .blade.php files are for views
            if ($forType == ImportDataHelper::FOLDER_IDENTIFIER_TEMPLATE) {
                $data = new Entities\Template(slug: $slug);
                $this->importDataService->addTemplate(
                    slug: $data->slug,
                    data: $data
                );

                return;
            }

            $jsonData = $fs->json($file);

            if (is_null($jsonData)) {
                return;
            }

            switch ($forType) {
                case ImportDataHelper::FOLDER_IDENTIFIER_DOCUMENTTYPE:
                    $data = Entities\DocumentType::fromArray($jsonData);
                    $this->importDataService->addDocumentType(
                        slug: $slug,
                        data: $data
                    );

                    break;
                case ImportDataHelper::FOLDER_IDENTIFIER_FIELDGROUP:
                    $data = Entities\FieldGroup::fromArray(Arr::except($jsonData, 'fields'));
                    $fields = Arr::map($jsonData['fields'] ?? [], fn ($i) => Entities\Field::fromArray($i));
                    $this->importDataService->addFieldGroup(
                        slug: $slug,
                        data: $data,
                        fields: $fields
                    );

                    break;
                case ImportDataHelper::FOLDER_IDENTIFIER_CONTENT:
                    $data = Entities\Content::fromArray($jsonData);
                    $this->importDataService->addContent(
                        slug: $slug,
                        parent: $data->parent,
                        data: $data
                    );

                    break;
                case ImportDataHelper::FOLDER_IDENTIFIER_NAVIGATION:
                    $data = Entities\Navigation::fromArray($jsonData);
                    $this->importDataService->addNavigation(data: $data);

                    break;
            }

        }, includeSubFolders: false, neededExtensions: ['.json', '.blade.php']);
    }

    /**
     * Duplicates view files from the specified folder path.
     *
     * @param  \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter  $fs  The filesystem instance.
     * @param  string  $folderPath  The path to the folder containing the view files to duplicate.
     * @param  string  $forType  The type of view files to duplicate.
     * @return array|null Error message for the file or null
     */
    protected function duplicateViewFiles($fs, $folderPath, $forType)
    {
        return static::fileProcessingWithCallback(fs: $fs, folderPath: $folderPath, callback: function ($fs, $folderPath, $file) use ($forType) {

            $subPathRelativeToFolder = (string) Str::of($file)->after($folderPath)->ltrim('/');

            $toPath = self::getViewFolderMapFor($forType, $subPathRelativeToFolder);

            $content = $fs->get($file);

            file_put_contents($toPath, $content);

        }, includeSubFolders: true, neededExtensions: ['.blade.php']);
    }

    protected static function getFolderNameFromExtractedPath($extractedPath)
    {
        return basename($extractedPath);
    }

    /**
     * Processes files in a specified folder and applies a callback function to each file.
     *
     * @param  \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter  $fs  The filesystem instance to use for file operations.
     * @param  string  $folderPath  The path to the folder containing the files to process.
     * @param  callable(\Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter, string, string): void  $callback  The callback function to apply to each file.
     * @param  bool  $includeSubFolders  Whether to include subfolders in the essing. Default is false.
     * @param  array  $neededExtensions  The file extension to filter files by. If empty, all files are processed.
     * @return array|null Error message for the file or null
     */
    protected static function fileProcessingWithCallback($fs, $folderPath, $callback, bool $includeSubFolders = false, ?array $neededExtensions = null)
    {
        $files = $includeSubFolders ? $fs->allFiles($folderPath) : $fs->files($folderPath);

        $failed = [];

        $neededExtensions = Arr::wrap($neededExtensions);

        foreach ($files as $file) {

            try {

                if (! empty($neededExtensions)) {

                    $haveAnyExtension = collect($neededExtensions)->contains(function ($ext) use ($file) {
                        return Str::endsWith($file, $ext);
                    });

                    if (! $haveAnyExtension) {
                        continue;
                    }
                }

                $callback($fs, $folderPath, $file);

            } catch (\Throwable $th) {

                $failed[$file] = [
                    'ex' => $th->getMessage(),
                    'trace' => $th->getTraceAsString(),
                ];

            }

        }

        return $failed;
    }

    protected static function getViewFolderMapFor($forType, $fileRelativePath)
    {
        return match ($forType) {
            ImportDataHelper::FOLDER_IDENTIFIER_VIEW => resource_path('views') . DIRECTORY_SEPARATOR . $fileRelativePath,
            ImportDataHelper::FOLDER_IDENTIFIER_TEMPLATE => InspireCmsConfig::get('template.path') . DIRECTORY_SEPARATOR . $fileRelativePath,
            default => null,
        };
    }
}
