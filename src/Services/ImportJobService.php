<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\ImportData\Entities;
use SolutionForest\InspireCms\ImportData\ZipFileReader;

class ImportJobService implements ImportJobServiceInterface
{
    /**
     * Constant representing the folder structure.
     *
     * @var array
     */
    const FOLDER_STRUCTURE = [
        self::FOLDER_IDENTIFIER_CONTENT,
        self::FOLDER_IDENTIFIER_DOCUMENTTYPE,
        self::FOLDER_IDENTIFIER_FIELDGROUP,
        self::FOLDER_IDENTIFIER_NAVIGATION,
        ...self::FOLDER_HAS_VIEWS,
    ];

    const FOLDER_HAS_VIEWS = [
        self::FOLDER_IDENTIFIER_VIEW,
        self::FOLDER_IDENTIFIER_TEMPLATE,
    ];

    const FOLDER_IDENTIFIER_CONTENT = 'Content';
    const FOLDER_IDENTIFIER_DOCUMENTTYPE = 'DocumentTypes';
    const FOLDER_IDENTIFIER_FIELDGROUP = 'FieldGroups';
    const FOLDER_IDENTIFIER_NAVIGATION = 'NavigationMenus';
    const FOLDER_IDENTIFIER_TEMPLATE = 'Templates';
    const FOLDER_IDENTIFIER_VIEW = 'Views';

    public function __construct(
        protected ImportDataServiceInterface $importDataService,
        protected ZipFileReader $zipFileReader,
    ) { }

    public static function getFileStructureHtml()
    {
        $html = '<b>Folder Structure of zip file:</b>';
        $html .= '<ul>';
        foreach (self::FOLDER_STRUCTURE as $folder) {
            $html .= '<li>' . $folder . '</li>';
        }
        $html .= '</ul>';
        return new HtmlString($html);
    }

    /** @inheritDoc */
    public function execute($job)
    {
        $extractorFs = null;
        $extractedFolderPath = null;

        $message = [];

        try {

            // Ensure the import data service is reset
            $this->importDataService->reset();
            
            [$jobFs, $filePath] = $job->getStorageAndFilePath();

            [$extractorFs, $extractedFolderPath] = $this->zipFileReader->readFromPath($jobFs->path($filePath));

            if (is_null($extractedFolderPath)) {
                throw new \Exception('The provided file is not a ZIP file.');
            }

            $folderPaths = $extractorFs->directories($extractedFolderPath);

            foreach ($folderPaths as $folderPath) {

                $folderName = static::getFolderNameFromExtractedPath($folderPath);

                // Fixed folder structure
                if (! in_array($folderName, self::FOLDER_STRUCTURE)) {
                    continue;
                }

                if (in_array($folderName, self::FOLDER_HAS_VIEWS)) {

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

            $job->markAsCompleted(
                empty($message) ? null : $message
            );

        } catch (\Throwable $th) {

            $message[] = [
                'exMessage' => $th->getMessage(),
                'exTrace' => $th->getTraceAsString(),
            ];
            
            $job->markAsFailed($message);

        } finally {
            // Delete the extracted folder
            if ($extractorFs != null && $extractedFolderPath != null && $extractorFs->exists($extractedFolderPath)) {
                $extractorFs->deleteDirectory($extractedFolderPath);
            }
        }
    }

    /**
     * @param \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter $fs The filesystem instance.
     * @param string $folderPath The path to the folder containing the view files to duplicate.
     * @param string $forType 
     * 
     * @return array|null Error message for the file or null
     */
    protected function importDataForType($fs, $folderPath, $forType)
    {
        $neededTypes = collect(array_flip(self::FOLDER_STRUCTURE))->except(self::FOLDER_IDENTIFIER_VIEW)->keys()->all();

        if (! in_array($forType, $neededTypes)) {
            return;
        }
        
        return static::fileProcessingWithCallback(fs: $fs, folderPath: $folderPath, callback: function ($fs, $folderPath, $file) use ($forType) {

            $slug = Str::before(basename($file), '.');

            // .blade.php files are for views
            if ($forType == self::FOLDER_IDENTIFIER_TEMPLATE) {
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
                case self::FOLDER_IDENTIFIER_DOCUMENTTYPE:
                    $data = Entities\DocumentType::fromArray($jsonData);
                    $this->importDataService->addDocumentType(
                        slug: $slug,
                        data: $data
                    );

                    break;
                case self::FOLDER_IDENTIFIER_FIELDGROUP:
                    $data = Entities\FieldGroup::fromArray(Arr::except($jsonData, 'fields'));
                    $fields = Arr::map($jsonData['fields'] ?? [], fn ($i) => Entities\Field::fromArray($i));
                    $this->importDataService->addFieldGroup(
                        slug: $slug,
                        data: $data,
                        fields: $fields
                    );

                    break;
                case self::FOLDER_IDENTIFIER_CONTENT:
                    $data = Entities\Content::fromArray($jsonData);
                    $this->importDataService->addContent(
                        slug: $slug,
                        parent: $data->parent,
                        data: $data
                    );

                    break;
                case self::FOLDER_IDENTIFIER_NAVIGATION:
                    $data = Entities\Navigation::fromArray($jsonData);
                    $this->importDataService->addNavigation(data: $data);

                    break;
            }
            
        }, includeSubFolders: false, neededExtensions: ['.json', '.blade.php']);
    }

    /**
     * Duplicates view files from the specified folder path.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter $fs The filesystem instance.
     * @param string $folderPath The path to the folder containing the view files to duplicate.
     * @param string $forType The type of view files to duplicate.
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
     * @param \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter $fs The filesystem instance to use for file operations.
     * @param string $folderPath The path to the folder containing the files to process.
     * @param callable(\Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter, string, string): void $callback The callback function to apply to each file.
     * @param bool $includeSubFolders Whether to include subfolders in the essing. Default is false.
     * @param array $neededExtensions The file extension to filter files by. If empty, all files are processed.
     *
     * @return array|null Error message for the file or null
     */
    protected static function fileProcessingWithCallback($fs, $folderPath, $callback, bool $includeSubFolders = false, array $neededExtensions = null)
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
            self::FOLDER_IDENTIFIER_VIEW => resource_path('views') . DIRECTORY_SEPARATOR . $fileRelativePath,
            self::FOLDER_IDENTIFIER_TEMPLATE => config('inspirecms.template.path') . DIRECTORY_SEPARATOR . $fileRelativePath,
            default => null,
        };
    }
}
