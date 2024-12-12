<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Helpers\FileHelper;
use SolutionForest\InspireCms\Helpers\ThrowableHelper;
use SolutionForest\InspireCms\ImportData\Entities;
use SolutionForest\InspireCms\ImportData\ZipFileReader;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Services\ImportServiceInterface;

class ImportService implements ImportServiceInterface
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
        self::FOLDER_IDENTIFIER_TEMPLATE,
        self::FOLDER_IDENTIFIER_VIEW,
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
    ) {}

    /** {@inheritDoc} */
    public static function getSampleFileStructure()
    {
        return collect(self::FOLDER_STRUCTURE)->mapWithKeys(function ($folder) {

            $sampleFiles = [];

            $maxRandomFiles = 2;

            $generateFiles = function ($filenamePrefix, $extension) use ($maxRandomFiles) {

                return collect(range(1, $maxRandomFiles))
                    ->map(function ($i) use ($filenamePrefix, $extension) {

                        $name = (string) Str::of($filenamePrefix)->snake()->singular()->replaceMatches('/[^a-z0-9]/', '-');

                        return "{$name}-{$i}{$extension}";

                    })
                    ->values()
                    ->all();
            };

            if ($folder == self::FOLDER_IDENTIFIER_VIEW) {

                $sampleFiles = array_merge([
                    'components' => $generateFiles('component', '.blade.php'),
                ], $generateFiles('sample', '.blade.php'));

            } elseif (in_array($folder, self::FOLDER_HAS_VIEWS)) {

                $sampleFiles = $generateFiles($folder, '.blade.php');

            } else {

                $sampleFiles = $generateFiles($folder, '.json');

            }

            return [$folder => $sampleFiles];

        })->all();
    }

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
        $sampleData = $this->generateSampleData();

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

    protected function generateSampleData()
    {
        $generateOrder = [
            self::FOLDER_IDENTIFIER_FIELDGROUP => collect(range(1, 3))->map(fn ($i) => "field-group-{$i}.json")->all(),
            self::FOLDER_IDENTIFIER_TEMPLATE => collect(range(1, 2))->map(fn ($i) => "template-{$i}.blade.php")->all(),
            self::FOLDER_IDENTIFIER_DOCUMENTTYPE => collect(range(1, 4))->map(fn ($i) => "document-type-{$i}.json")->all(),
            self::FOLDER_IDENTIFIER_CONTENT => collect(range(1, 1))->map(fn ($i) => "content-{$i}.json")->all(),
            self::FOLDER_IDENTIFIER_NAVIGATION => collect(range(1, 1))->map(fn ($i) => "navigation-{$i}.json")->all(),
        ];
        $data = [];

        $getRandomFileBaseNameOnFolder = fn ($folder, $number): array => !isset($generateOrder[$folder]) ? [] : collect($generateOrder[$folder])->random($number)->map(fn ($filename) => Str::before($filename, '.'))->all();

        foreach ($generateOrder as $folder => $sampleFileNames) {

            $sequence = [];

            switch ($folder) {
                case self::FOLDER_IDENTIFIER_DOCUMENTTYPE:
                    {
                        $arrayOrder = ['title', 'showAsTable', 'icon', 'templates', 'defaultTemplate', 'fieldGroups', 'inheritance', 'rejected'];
                        $sequence = collect([
                            [
                                "showAsTable" => false,
                                "templates" => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_TEMPLATE, 1),
                                "fieldGroups" => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_FIELDGROUP, 1),
                            ],
                            [
                                "showAsTable" => true,
                                "templates" => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_TEMPLATE, 1),
                                "fieldGroups" => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_TEMPLATE, 1),
                            ],
                            [
                                "showAsTable" => false,
                                "templates" => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_TEMPLATE, 2),
                                "fieldGroups" => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_TEMPLATE, 1),
                            ],
                            [
                                "showAsTable" => false,
                                "templates" => [],
                                "fieldGroups" => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_TEMPLATE, 1),
                            ]
                        ])
                        ->map(fn (array $item): array => collect(['defaultTemplate' => Arr::first($item['templates'] ?? null)])
                            ->merge(['title' => null, 'icon' => null, 'rejected' => []])
                            ->merge($item)
                            ->sortBy(fn ($value, $key) => array_search($key, $arrayOrder))->all()
                        )
                        ->all();
                    }
                    break;
                case self::FOLDER_IDENTIFIER_FIELDGROUP:
                    {
                        $sequence = collect([
                            [
                                new Entities\Field(
                                    slug: 'field-1',
                                    type: 'translate',
                                    config: ['field' => 'text'],
                                    label: 'Field 1',
                                ),
                                new Entities\Field(
                                    slug: 'field-2',
                                    type: 'mediaPicker',
                                    config: ['mimeTypes' => ['image'], 'multiple' => false],
                                    label: 'Field 2',
                                ),
                            ], [
                                new Entities\Field(
                                    slug: 'field-3',
                                    type: 'contentPicker',
                                    config: ['documentType' => 'article', 'multiple' => true],
                                    label: 'Field 3',
                                ),
                                new Entities\Field(
                                    slug: 'field-4',
                                    type: 'text',
                                    config: [],
                                    label: 'Field 4',
                                ),
                            ], [
                                new Entities\Field(
                                    slug: 'field-5',
                                    type: 'text',
                                    config: [],
                                    label: 'Field 5',
                                ),
                            ]
                        ])->map(fn (array $fields): array => ['title' => null, 'fields' => collect($fields)->map(fn ($field) => $field->toArray())->all()])->all();
                    }
                    break;
                case self::FOLDER_IDENTIFIER_CONTENT:
                    {
                        $arrayOrder = ['slug','title','documentType','isDefault','properties','publishState','sitemap','webSetting','parent','template'];
                        $sequence = collect([
                            [
                                "documentType" => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_DOCUMENTTYPE, 1)[0] ?? null,
                                "publishState" => "publish",
                                "properties" => []
                            ]
                        ])->map(fn (array $item, $i): array => collect(['title' => ['en' => "Content {$i}", 'fr' => "Content {$i}"]])
                            ->merge($item)
                            ->sortBy(fn ($value, $key) => array_search($key, $arrayOrder))
                            ->all()
                        )->all();
                    }
                    break;
                case self::FOLDER_IDENTIFIER_NAVIGATION:
                    {
                        $arrayOrder = ['id', 'category', 'type', 'title', 'contentSlugPath', 'url', 'target', 'children'];
                        $sequence = collect([
                            new Entities\Navigation(category: 'main', title: ['en' => 'Main', 'fr' => 'Main'],  type: 'content', contentSlugPath: $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_CONTENT, 1)[0] ?? null),
                        ])
                        ->map(fn ($item) => collect($item->toArray())->sortBy(fn ($value, $key) => array_search($key, $arrayOrder))->all())
                        ->all();
                    }
                    break;
                case self::FOLDER_IDENTIFIER_TEMPLATE:
                    {
                        $sequence = [
                            '<x-dynamic-component :component="\SolutionForest\InspireCms\InspireCmsConfig::getComponentWithTheme(\'page\')" :content="$content" :locale="$locale ?? $content->getLocale()">This is sample view</x-dynamic-component>',
                        ];
                    }
                    break;
            }

            $fileContentArr = [];

            foreach ($sampleFileNames as $i => $filename) {
                $targetDataFromSequence = $i === 0 ? $sequence[$i] : $sequence[$i % count($sequence)];
                $fileContentArr[$filename] = is_array($targetDataFromSequence) ? json_encode($targetDataFromSequence, JSON_PRETTY_PRINT) : $targetDataFromSequence;
            }

            $data[$folder] = $fileContentArr;
        }
        
        return $data;
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
            self::FOLDER_IDENTIFIER_VIEW => resource_path('views') . DIRECTORY_SEPARATOR . $fileRelativePath,
            self::FOLDER_IDENTIFIER_TEMPLATE => InspireCmsConfig::get('template.path') . DIRECTORY_SEPARATOR . $fileRelativePath,
            default => null,
        };
    }
}
