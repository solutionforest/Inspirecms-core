<?php

namespace SolutionForest\InspireCms\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory;
use SolutionForest\InspireCms\ImportData\Entities;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;

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
        self::FOLDER_IDENTIFIER_LANGUAGE,
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

    const FOLDER_IDENTIFIER_LANGUAGE = 'Languages';

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

                } elseif ($folder == self::FOLDER_IDENTIFIER_LANGUAGE) {

                    $children = collect(['en', 'fr'])
                        ->map(fn ($locale) => "{$locale}.json")
                        ->map(fn ($filename) => $getReturnValue($filename))
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
        $locales = ['en', 'fr'];
        $generateOrder = [
            self::FOLDER_IDENTIFIER_LANGUAGE => collect($locales)->map(fn ($locale) => "{$locale}.json")->all(),
            self::FOLDER_IDENTIFIER_FIELDGROUP => collect(range(1, 3))->map(fn ($i) => "field-group-{$i}.json")->all(),
            self::FOLDER_IDENTIFIER_TEMPLATE => collect(range(1, 5))->map(fn ($i) => array_map(fn ($j) => ["template-{$i}" . DIRECTORY_SEPARATOR . "theme-{$j}.blade.php"], range(1, 2)))->flatten()->all(),
            self::FOLDER_IDENTIFIER_DOCUMENTTYPE => collect(range(1, 4))->map(fn ($i) => "document-type-{$i}.json")->all(),
            self::FOLDER_IDENTIFIER_CONTENT => collect(range(1, 1))->map(fn ($i) => "content-{$i}.json")->all(),
            self::FOLDER_IDENTIFIER_NAVIGATION => collect(range(1, 2))->map(fn ($i) => "navigation-{$i}.json")->all(),
        ];
        $data = [];

        foreach ($generateOrder as $folder => $sampleFileNames) {

            $getSampleDataInSequence = function (string $folder, int $index, string $filename) use ($generateOrder, $locales) {

                $getRandomFileBaseNameOnFolder = fn ($folder, $number): array => ! isset($generateOrder[$folder]) ? [] : collect($generateOrder[$folder])->random($number)->map(fn ($filename) => Str::before($filename, '.'))->all();
                $getRandomTemplateSlug = fn ($number): array => collect($getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_TEMPLATE, $number))
                    ->map(fn ($path) => str($path)->explode('/')->first())
                    ->all();

                $generateTranslationArray = fn (array $propsAndValueMap): array => collect($propsAndValueMap)->map(fn ($value) => collect($locales)->mapWithKeys(fn ($locale) => ["{$locale}" => $value])->all())->all();

                $itemSlug = Str::before($filename, '.');

                $sequence = [];

                switch ($folder) {

                    case self::FOLDER_IDENTIFIER_DOCUMENTTYPE:
                        $sequence = collect([
                            [
                                'showAsTable' => false,
                                'showAtRoot' => true,
                                'templates' => $getRandomTemplateSlug(1),
                                'fieldGroups' => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_FIELDGROUP, 1),
                            ],
                            [
                                'showAsTable' => true,
                                'showAtRoot' => true,
                                'templates' => [],
                                'fieldGroups' => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_FIELDGROUP, 1),
                            ],
                            [
                                'showAsTable' => false,
                                'showAtRoot' => false,
                                'category' => DocumentTypeCategory::Data,
                                'templates' => $getRandomTemplateSlug(2),
                                'fieldGroups' => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_FIELDGROUP, 1),
                            ],
                            [
                                'showAsTable' => false,
                                'showAtRoot' => false,
                                'templates' => [],
                                'fieldGroups' => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_FIELDGROUP, 1),
                            ],
                        ])
                            ->map(
                                fn (array $item): array => collect(['defaultTemplate' => Arr::first($item['templates'] ?? null)])
                                    ->merge(['slug' => $itemSlug])
                                    ->merge(['title' => Str::title($itemSlug)])
                                    ->merge($item)
                                    ->all()
                            )
                            ->map(fn (array $item) => Entities\DocumentType::fromArray($item)->toArray())
                            ->all();

                        break;

                    case self::FOLDER_IDENTIFIER_FIELDGROUP:

                        $sequence = collect([
                            [
                                new Entities\Field(
                                    slug: 'field-1',
                                    label: 'Field 1',
                                    type: 'text',
                                    config: ['translatable' => true],
                                ),
                                new Entities\Field(
                                    slug: 'field-2',
                                    label: 'Field 2',
                                    type: 'mediaPicker',
                                    config: ['mimeTypes' => ['image'], 'max' => 1],
                                ),
                            ], [
                                new Entities\Field(
                                    slug: 'field-3',
                                    label: 'Field 3',
                                    type: 'contentPicker',
                                    config: ['documentType' => 'article'],
                                ),
                                new Entities\Field(
                                    slug: 'field-4',
                                    label: 'Field 4',
                                    type: 'text',
                                    config: [],
                                ),
                            ], [
                                new Entities\Field(
                                    slug: 'field-5',
                                    label: 'Field 5',
                                    type: 'text',
                                    config: [],
                                ),
                            ],
                        ])
                            ->map(fn (array $fields, $index) => new Entities\FieldGroup(
                                slug: $itemSlug,
                                title: Str::title($itemSlug),
                                fields: $fields,
                            ))
                            ->map(fn (Entities\FieldGroup $fieldGroup) => $fieldGroup->toArray())
                            ->all();

                        break;

                    case self::FOLDER_IDENTIFIER_CONTENT:

                        $sequence = collect([
                            [
                                'documentType' => 'document-type-1',
                                'publishState' => 'publish',
                                'properties' => [],
                            ],
                        ])
                            ->map(function (array $item) use ($itemSlug, $generateTranslationArray) {
                                $title = Str::title($itemSlug);

                                return collect($item)
                                    ->merge(['slug' => $itemSlug])
                                    ->merge($generateTranslationArray(['title' => $title]))
                                    ->merge([
                                        'sitemap' => [
                                            'priority' => 0.5,
                                            'change_frequency' => 'monthly',
                                            'enable' => true,
                                        ],
                                        'webSetting' => [
                                            'seo' => [
                                                'meta_title' => $title,
                                                'meta_description' => [],
                                                'meta_keywords' => [],
                                                'og_title' => $title,
                                                'og_description' => [],
                                                'og_image' => [],
                                            ],
                                            'robots' => [
                                                'index' => true,
                                                'follow' => true,
                                            ],
                                            'redirect_path' => null,
                                            'redirect_content_id' => KeyHelper::generateMinUuid(),
                                            'redirect_type' => null,
                                        ],
                                    ])
                                    ->all();
                            })
                            ->map(fn (array $item) => Entities\Content::fromArray($item)->toArray())
                            ->all();

                        break;

                    case self::FOLDER_IDENTIFIER_NAVIGATION:

                        $sequence = collect([
                            [
                                'category' => 'main',
                                ...$generateTranslationArray(['title' => 'Main']),
                                'type' => 'content',
                                'contentSlugPath' => $getRandomFileBaseNameOnFolder(self::FOLDER_IDENTIFIER_CONTENT, 1)[0] ?? null,
                            ],
                            [
                                'category' => 'footer',
                                ...$generateTranslationArray(['title' => 'Footer']),
                                'type' => 'group',
                                'children' => [
                                    [
                                        'category' => 'footer',
                                        ...$generateTranslationArray(['title' => 'About Us']),
                                        'type' => 'link',
                                        'url' => '/about-us',
                                    ],
                                    [
                                        'category' => 'footer',
                                        ...$generateTranslationArray(['title' => 'Contact Us']),
                                        'type' => 'link',
                                        'url' => '/contact-us',
                                    ],
                                ],
                            ],
                        ])
                            ->map(fn (array $item) => Entities\Navigation::fromArray($item)->toArray())
                            ->all();

                        break;

                    case self::FOLDER_IDENTIFIER_TEMPLATE:

                        $sequence = [
                            TemplateHelper::retrieveDefaultThemeContent(),
                        ];

                        break;

                    case self::FOLDER_IDENTIFIER_LANGUAGE:

                        $sequence = collect($locales)
                            ->map(fn ($locale) => new Entities\Language(
                                code: $locale,
                                isDefault: $locale == 'en',
                            ))
                            ->map(fn (Entities\Language $language) => $language->toArray())
                            ->all();

                        break;
                }

                $targetSequenceIndex = $index === 0 ? $index : $index % count($sequence);

                if ($targetSequenceIndex <= 0) {
                    return Arr::first($sequence);
                }

                return $sequence[$targetSequenceIndex] ?? [];
            };

            $fileContentArr = [];

            foreach ($sampleFileNames as $i => $filename) {

                $targetDataFromSequence = $getSampleDataInSequence($folder, $i, $filename);

                $fileContentArr[$filename] = is_array($targetDataFromSequence) ? json_encode($targetDataFromSequence, JSON_PRETTY_PRINT) : $targetDataFromSequence;

            }

            $data[$folder] = $fileContentArr;
        }

        return $data;
    }

    public static function getDiskDriver(): string
    {
        return strval(InspireCmsConfig::get('import_export.imports.disk', 'local'));
    }

    public static function getDirectory(): string
    {
        return strval(InspireCmsConfig::get('import_export.imports.directory', 'imports'));
    }

    public static function getTempDiskDriver(): string
    {
        return strval(InspireCmsConfig::get('import_export.imports.temporary.disk', 'local'));
    }

    public static function getTempDirectory(): string
    {
        return strval(InspireCmsConfig::get('import_export.imports.temporary.directory', 'temp/imports'));
    }

    public static function getAllowedMimeTypes(): array
    {
        return InspireCmsConfig::get('import_export.imports.allowed_mime_types', []);
    }

    public static function getMaxFileSize(): int
    {
        return (int) InspireCmsConfig::get('import_export.imports.max_file_size', 2048);
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
