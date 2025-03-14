<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Helpers\FileHelper;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\Helpers\ThrowableHelper;
use SolutionForest\InspireCms\ImportData\Entities;
use SolutionForest\InspireCms\ImportData\ZipFileReader;

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

                if (in_array($folderName, [ImportDataHelper::FOLDER_IDENTIFIER_VIEW])) {

                    $failedForViews = $this->duplicateViewFiles(fs: $extractorFs, folder: $folderPath, forType: $folderName);
                    if (! empty($failedForViews)) {
                        $message[] = [
                            'message' => "Failed to duplicate view files for {$folderName}.",
                            'files' => $failedForViews,
                        ];
                    }

                }

                $failedForImportData = $this->importDataForType(fs: $extractorFs, folder: $folderPath, forType: $folderName);
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
     * @param  string  $folder  The path to the folder containing the view files to duplicate.
     * @param  string  $forType
     * @return array|null Error message for the file or null
     */
    protected function importDataForType($fs, $folder, $forType)
    {
        $neededTypes = collect(array_flip(ImportDataHelper::FOLDER_STRUCTURE))->except(ImportDataHelper::FOLDER_IDENTIFIER_VIEW)->keys()->all();

        if (! in_array($forType, $neededTypes)) {
            return;
        }

        if ($forType == ImportDataHelper::FOLDER_IDENTIFIER_TEMPLATE) {

            return static::fileProcessingWithCallback(
                fs: $fs,
                folder: $folder,
                includeSubFolders: true,
                neededExtensions: ['.blade.php'],
                callback: function ($fs, $folder, $filePath) use ($forType) {
                    if ($forType == ImportDataHelper::FOLDER_IDENTIFIER_TEMPLATE) {

                        [$slug, $theme] = static::extractTemplateSlugAndFilename($filePath, $folder);

                        $themeContent = $fs->get($filePath) ?? TemplateHelper::retrieveDefaultThemeContent();

                        $data = new Entities\Template(slug: $slug, content: [$theme => $themeContent]);

                        $this->importDataService->addTemplate(
                            slug: $data->slug,
                            data: $data
                        );

                        return;
                    }

                }
            );
        }

        return static::fileProcessingWithCallback(
            fs: $fs,
            folder: $folder,
            includeSubFolders: false,
            neededExtensions: ['.json'],
            callback: function ($fs, $folderPath, $file) use ($forType) {

                $slug = Str::before(basename($file), '.');

                $jsonData = $fs->json($file);

                if (is_null($jsonData)) {
                    throw new \Exception('Invalid JSON data.');
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
                        $data->fields = $fields;
                        $this->importDataService->addFieldGroup(
                            slug: $data->slug,
                            data: $data,
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

            }
        );
    }

    /**
     * Duplicates view files from the specified folder path.
     *
     * @param  \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter  $fs  The filesystem instance.
     * @param  string  $folder  The path to the folder containing the view files to duplicate.
     * @param  string  $forType  The type of view files to duplicate.
     * @return array|null Error message for the file or null
     */
    protected function duplicateViewFiles($fs, $folder, $forType)
    {
        return static::fileProcessingWithCallback(
            fs: $fs,
            folder: $folder,
            includeSubFolders: true,
            neededExtensions: ['.blade.php'],
            callback: function ($fs, $folder, $filePath) use ($forType) {

                switch ($forType) {
                    case ImportDataHelper::FOLDER_IDENTIFIER_VIEW:
                        $toPath = resource_path('views/' . Str::of($filePath)->after($folder)->ltrim('/')->toString());

                        break;

                    default:
                        $toPath = null;

                        break;
                }

                if (is_null($toPath)) {
                    return;
                }

                $content = $fs->get($filePath);

                // Create the directory if it does not exist
                FileHelper::ensureDirectoryExists(dirname($toPath));

                file_put_contents($toPath, $content);

            }
        );
    }

    protected static function getFolderNameFromExtractedPath($extractedPath)
    {
        return basename($extractedPath);
    }

    /**
     * Processes files in a specified folder and applies a callback function to each file.
     *
     * @param  \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter  $fs  The filesystem instance to use for file operations.
     * @param  string  $folder  The path to the folder containing the files to process.
     * @param  callable(\Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter, string, string): void  $callback  The callback function to apply to each file.
     * @param  bool  $includeSubFolders  Whether to include subfolders in the essing. Default is false.
     * @param  array  $neededExtensions  The file extension to filter files by. If empty, all files are processed.
     * @return array|null Error message for the file or null
     */
    protected static function fileProcessingWithCallback($fs, $folder, $callback, bool $includeSubFolders = false, ?array $neededExtensions = null)
    {
        $files = $includeSubFolders ? $fs->allFiles($folder) : $fs->files($folder);

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

                $callback($fs, $folder, $file);

            } catch (\Throwable $th) {

                $failed[$file] = [
                    'ex' => $th->getMessage(),
                    'trace' => $th->getTraceAsString(),
                ];

            }

        }

        return $failed;
    }

    protected static function extractTemplateSlugAndFilename(string $file, string $folder)
    {
        [$templateSlug, $themeFilename] = Str::of($file)->after($folder)->ltrim('/')->explode('/', 2);

        $themeFilename = str($themeFilename)->replace('.blade.php', '')->toString();

        return [$templateSlug, $themeFilename];
    }
}
