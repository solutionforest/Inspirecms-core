<?php

namespace SolutionForest\InspireCms\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\ImportData\Entities;
use SolutionForest\InspireCms\InspireCmsConfig;

class ImportDataHelper
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

    /**
     * Get the sample file structure for import jobs.
     *
     * This method returns an array representing the structure of a sample file
     * that can be used for import jobs. The structure typically includes the
     * necessary headers and format required for a successful import.
     *
     * @return array The sample file structure.
     */
    public static function getSampleFileStructure()
    {
        return collect(self::FOLDER_STRUCTURE)
            ->map(function ($folder) {

                $children = [];

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

                $getItemIcon = function ($file) {
                    $icon = 'heroicon-o-document';
                    $iconAlias = null;
                    if (Str::endsWith($file, '.json')) {
                        $icon = null;
                        $iconAlias = 'inspirecms::json-file';
                    }

                    return [$icon, $iconAlias];
                };

                $getReturnValue = fn ($filename, $icon = null) => [
                    'title' => $filename,
                    'icon' => $icon ?? $getItemIcon($filename)[0],
                    'iconAlias' => $icon ?? $getItemIcon($filename)[1],
                ];

                if ($folder == self::FOLDER_IDENTIFIER_VIEW) {

                    $children = collect([
                        array_merge(
                            $getReturnValue('component', 'heroicon-o-folder'),
                            ['children' => collect($generateFiles('component', '.blade.php'))->map(fn ($filename) => $getReturnValue($filename))->all()]
                        ),
                    ])
                        ->merge(
                            collect($generateFiles('sample', '.blade.php'))
                                ->map(fn ($filename) => $getReturnValue($filename))
                        )
                        ->all();

                } elseif ($folder == self::FOLDER_IDENTIFIER_TEMPLATE) {

                    $children = collect($generateFiles($folder, ''))
                        ->map(fn ($templateFolder) => array_merge(
                            $getReturnValue($templateFolder, 'heroicon-o-folder'),
                            ['children' => collect($generateFiles('theme', '.blade.php'))->map(fn ($filename) => $getReturnValue($filename))->all()]
                        ))
                        ->all();

                } else {

                    $children = collect($generateFiles($folder, '.json'))
                        ->map(fn ($filename) => $getReturnValue($filename))
                        ->all();
                }

                return array_merge($getReturnValue($folder, 'heroicon-o-folder'), ['children' => $children]);

            })
            ->all();
    }

    public static function generateSampleData()
    {
        $generateOrder = [
            self::FOLDER_IDENTIFIER_FIELDGROUP => collect(range(1, 3))->map(fn ($i) => "field-group-{$i}.json")->all(),
            self::FOLDER_IDENTIFIER_TEMPLATE => collect(range(1, 2))->map(fn ($i) => array_map(fn ($j) => ["template-{$i}" . DIRECTORY_SEPARATOR . "theme-{$j}.blade.php"], range(1, 2)))->flatten()->all(),
            self::FOLDER_IDENTIFIER_DOCUMENTTYPE => collect(range(1, 4))->map(fn ($i) => "document-type-{$i}.json")->all(),
            self::FOLDER_IDENTIFIER_CONTENT => collect(range(1, 1))->map(fn ($i) => "content-{$i}.json")->all(),
            self::FOLDER_IDENTIFIER_NAVIGATION => collect(range(1, 1))->map(fn ($i) => "navigation-{$i}.json")->all(),
        ];
        $data = [];

        $getRandomFileBaseNameOnFolder = fn ($folder, $number): array => ! isset($generateOrder[$folder]) ? [] : collect($generateOrder[$folder])->random($number)->map(fn ($filename) => Str::before($filename, '.'))->all();

        foreach ($generateOrder as $folder => $sampleFileNames) {

            $sequence = [];

            switch ($folder) {
                case self::FOLDER_IDENTIFIER_DOCUMENTTYPE:
                    $arrayOrder = ['title', 'showAsTable', 'category', 'icon', 'templates', 'defaultTemplate', 'fieldGroups', 'inheritance', 'rejected'];
                    $sequence = collect([
                        [
                            'showAsTable' => false,
                            'category' => \SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory::Web->value,
                            'templates' => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_TEMPLATE, 1),
                            'fieldGroups' => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_FIELDGROUP, 1),
                        ],
                        [
                            'showAsTable' => true,
                            'category' => \SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory::Web->value,
                            'templates' => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_TEMPLATE, 1),
                            'fieldGroups' => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_TEMPLATE, 1),
                        ],
                        [
                            'showAsTable' => false,
                            'category' => \SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory::Data->value,
                            'templates' => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_TEMPLATE, 2),
                            'fieldGroups' => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_TEMPLATE, 1),
                        ],
                        [
                            'showAsTable' => false,
                            'category' => \SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory::Web->value,
                            'templates' => [],
                            'fieldGroups' => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_TEMPLATE, 1),
                        ],
                    ])
                        ->map(
                            fn (array $item): array => collect(['defaultTemplate' => Arr::first($item['templates'] ?? null)])
                                ->merge(['title' => null, 'icon' => null, 'rejected' => []])
                                ->merge($item)
                                ->sortBy(fn ($value, $key) => array_search($key, $arrayOrder))->all()
                        )
                        ->all();

                    break;
                case self::FOLDER_IDENTIFIER_FIELDGROUP:

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
                        ],
                    ])->map(fn (array $fields): array => ['title' => null, 'fields' => collect($fields)->map(fn ($field) => $field->toArray())->all()])->all();

                    break;
                case self::FOLDER_IDENTIFIER_CONTENT:

                    $arrayOrder = ['slug', 'title', 'documentType', 'isDefault', 'properties', 'publishState', 'sitemap', 'webSetting', 'parent', 'template'];
                    $sequence = collect([
                        [
                            'documentType' => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_DOCUMENTTYPE, 1)[0] ?? null,
                            'publishState' => 'publish',
                            'properties' => [],
                        ],
                    ])->map(
                        fn (array $item, $i): array => collect(['title' => ['en' => "Content {$i}", 'fr' => "Content {$i}"]])
                            ->merge($item)
                            ->sortBy(fn ($value, $key) => array_search($key, $arrayOrder))
                            ->all()
                    )->all();

                    break;
                case self::FOLDER_IDENTIFIER_NAVIGATION:

                    $arrayOrder = ['id', 'category', 'type', 'title', 'contentSlugPath', 'url', 'target', 'children'];
                    $sequence = collect([
                        new Entities\Navigation(category: 'main', title: ['en' => 'Main', 'fr' => 'Main'], type: 'content', contentSlugPath: $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_CONTENT, 1)[0] ?? null),
                    ])
                        ->map(fn ($item) => collect($item->toArray())->sortBy(fn ($value, $key) => array_search($key, $arrayOrder))->all())
                        ->all();

                    break;
                case self::FOLDER_IDENTIFIER_TEMPLATE:

                    $sequence = [
                        '<x-dynamic-component :component="inspirecms_templates()->getComponentWithTheme(\'page\')" :content="$content" :locale="$locale ?? $content->getLocale()">This is sample view</x-dynamic-component>',
                    ];

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

    public static function getDiskDriver(): string
    {
        return strval(InspireCmsConfig::get('imports.disk', 'local'));
    }

    public static function getTempDiskDriver(): string
    {
        return strval(InspireCmsConfig::get('imports.temp_disk', 'local'));
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

    public static function retrieveClearanceDaysInterval()
    {
        return InspireCmsConfig::get('models.prunable.import.interval', 5);
    }
}
