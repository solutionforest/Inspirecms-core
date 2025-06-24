<?php

namespace SolutionForest\InspireCms\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Helpers\ModelHelper;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\ImportData\Entities as ImportDataEntities;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Services\ContentServiceInterface;
use SolutionForest\InspireCms\Services\ImportDataServiceInterface;
use SolutionForest\InspireCms\Support\Dtos\MediaAssetDto;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class SampleSeeder extends Seeder
{
    protected $importDataService;

    protected $contentService;

    protected array $mediaAssets = [];

    protected array $language = [];

    protected array $templates = [];

    protected array $fieldGroups = [];

    protected array $documentTypes = [];

    protected array $content = [];

    public function __construct(ImportDataServiceInterface $importDataService, ContentServiceInterface $contentService)
    {
        $this->importDataService = $importDataService;
        $this->contentService = $contentService;
    }

    public function run()
    {
        $this->makeSampleMedia();

        $this->makeSampleLanguages();
        $this->addSampleFields();
        $this->addSampleDocumentTypes();
        $this->addSampleContent();
        $this->addSampleNavigation();
        $this->addSampleTemplates();

        $this->importDataService->run();
        $this->showImportErrors();

        // Reset for next import
        $this->importDataService->reset();

        // ****
        // Configure the contentPicker field and data
        // ****

        // update config of contentPicker field for featured_posts
        if (($dtPost = InspireCmsConfig::getDocumentTypeModelClass()::firstWhere('slug', 'post-page'))
            && ($fFeaturedPosts = collect($this->getSampleFields())->first(fn (ImportDataEntities\FieldGroup $v) => $v->slug === 'featured_posts'))
        ) {
            $fFeaturedPosts->fields = collect($fFeaturedPosts->fields)
                ->map(function (ImportDataEntities\Field $field) use ($dtPost) {
                    if ($field->slug === 'posts') {
                        $field->config = array_merge($field->config ?? [], [
                            'documentType' => $dtPost->getKey(),
                        ]);
                    }

                    return $field;
                })
                ->all();
            $this->importDataService->addFieldGroup(
                data: $fFeaturedPosts,
            );
        }

        // handle the content have contentPicker field
        if (
            ($cPostPages = $this->contentService->getUnderRealPath(path: 'home/blog', limit: 10))
            && $cPostPages->isNotEmpty()
            && ($cBlogPage = collect($this->getSampleContent())->first(fn (ImportDataEntities\Content $v) => $v->slug === 'blog' && $v->parent === 'home'))
        ) {
            $cBlogPage->properties['featured_posts']['posts'] = $cPostPages->random(3)->map(fn ($item) => $item->getKey())->all();
            $this->importDataService->addContent(
                data: $cBlogPage
            );
        }

        $this->importDataService->run();
        $this->showImportErrors();
    }

    private function showImportErrors(): void
    {
        foreach ($this->importDataService->getErrors() as $type => $error) {
            if (is_string($error)) {
                $this->command->error('Having error: ' . $type . ' => ' . $error);
            } elseif ($error instanceof Collection || is_array($error)) {
                foreach ($error as $item) {
                    if ($item instanceof Model) {
                        $this->command->error('Having error: ' . $type . ' => ' . $item->getKey() . ': ' . $item->getErrorsAsString());
                    } elseif (is_string($item)) {
                        $this->command->error('Having error: ' . $type . ' => ' . $item);
                    } elseif ($item instanceof \Throwable) {
                        $this->command->error('Having error: ' . $type . ' => ' . $item->getMessage());
                    }
                }
            }
        }
    }

    protected function addSampleTemplates(): void
    {
        $allTemplates = app(\Illuminate\Filesystem\Filesystem::class)->allFiles(__DIR__ . '/../../stubs/SampleTemplates');

        $getContent = function (string $slug, string $theme) use ($allTemplates) {
            try {
                $file = collect($allTemplates)
                    ->first(fn (\SplFileInfo $file) => $file->getRelativePath() == $theme && $file->getFilenameWithoutExtension() == (string) str($slug)->title()->replace('-', ''));

                if (! $file) {

                    return null;
                }

                return $file->getContents();

            } catch (\Throwable $th) {
                return null;
            }
        };

        $slugs = collect($this->getSampleDocumentTypes())->flatMap(fn ($item) => $item->templates)->unique()->values()->toArray();
        $themes = TemplateHelper::getDefaultTemplateThemes();

        foreach ($slugs as $slug) {
            $themedContent = collect($themes)
                ->mapWithKeys(fn ($theme) => [$theme => $getContent($slug, $theme)])
                ->filter()
                ->toArray();
            $this->importDataService->addTemplate(
                new ImportDataEntities\Template(slug: $slug, content: $themedContent),
            );
        }
    }

    protected function getSampleFields(): array
    {
        $toolbarButtonsForRichEditor = array_keys(\SolutionForest\InspireCms\Fields\Configs\RichEditor::getAllAvailableToolbarButtons());
        $extraConfigForRichEditor = [
            'fileAttachmentsDisk' => 'public',
            'fileAttachmentsVisibility' => 'public',
        ];
        $items[] = new ImportDataEntities\FieldGroup(
            slug: 'contact_information',
            fields: [
                new ImportDataEntities\Field(slug: 'email', type: 'email'),
                new ImportDataEntities\Field(slug: 'phone', type: 'text'),
            ]
        );
        $items[] = new ImportDataEntities\FieldGroup(
            slug: 'featured_posts',
            fields: [
                new ImportDataEntities\Field(slug: 'posts', type: 'contentPicker', config: ['translatable' => false, 'documentType' => 'post-page']),
            ],
        );
        $items[] = new ImportDataEntities\FieldGroup(
            slug: 'hero_banner',
            fields: [
                new ImportDataEntities\Field(slug: 'brief', type: 'richEditor', config: ['translatable' => true, 'fileAttachmentsDisk' => 'public', 'toolbarButtons' => $toolbarButtonsForRichEditor, ...$extraConfigForRichEditor]),
                new ImportDataEntities\Field(slug: 'image_slider', type: 'mediaPicker', config: ['types' => ['image']]),
            ],
        );
        $items[] = new ImportDataEntities\FieldGroup(
            slug: 'page_banner',
            fields: [
                new ImportDataEntities\Field(slug: 'title', type: 'text', config: ['translatable' => true]),
                new ImportDataEntities\Field(slug: 'description', type: 'text', config: ['translatable' => true]),
                new ImportDataEntities\Field(slug: 'image', type: 'mediaPicker', config: ['types' => ['image'], 'max' => 1]),
            ],
        );
        $items[] = new ImportDataEntities\FieldGroup(
            slug: 'post_content',
            fields: [
                new ImportDataEntities\Field(slug: 'categories', type: 'tags', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'tags', type: 'tags', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'content', type: 'richEditor', config: ['translatable' => true, 'fileAttachmentsDisk' => 'public', 'toolbarButtons' => $toolbarButtonsForRichEditor, ...$extraConfigForRichEditor]),
            ],
        );
        $items[] = new ImportDataEntities\FieldGroup(
            slug: 'social_media',
            fields: [
                new ImportDataEntities\Field(slug: 'github', type: 'text'),
                new ImportDataEntities\Field(slug: 'twitter', type: 'text'),
                new ImportDataEntities\Field(slug: 'instagram', type: 'text'),
                new ImportDataEntities\Field(slug: 'linkedin', type: 'text'),
                new ImportDataEntities\Field(slug: 'facebook', type: 'text'),
            ]
        );

        return $items;
    }

    protected function addSampleFields()
    {
        foreach ($this->getSampleFields() as $group) {
            $this->importDataService->addFieldGroup($group);
        }
    }

    protected function addSampleDocumentTypes(): void
    {
        foreach ($this->getSampleDocumentTypes() as $item) {
            $this->importDataService->addDocumentType($item);
        }
    }

    /**
     * @return ImportDataEntities\DocumentType[]
     */
    protected function getSampleDocumentTypes()
    {
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'homepage',
            showAsTable: false,
            showAtRoot: true,
            category: 'web',
            icon: 'heroicon-o-home',
            templates: ['index'],
            defaultTemplate: 'index',
            fieldGroups: [
                'hero_banner',
            ],
            inheritance: [], // ['general-page-banner'],
            allowed: [
                'general-page',
                'settings',
                'blog-page',
            ],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'general-page',
            showAsTable: false,
            showAtRoot: false,
            category: 'web',
            icon: 'heroicon-o-document',
            templates: [
                'general-page',
                'about-us',
                'contact-us',
            ],
            defaultTemplate: 'general-page',
            fieldGroups: [
                'page_banner',
            ],
            inheritance: [], // ['general-page-banner'],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'settings',
            showAsTable: false,
            showAtRoot: false,
            category: 'data',
            icon: 'heroicon-o-cog-6-tooth',
            fieldGroups: [
                'social_media',
                'contact_infomation',
            ],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'blog-page',
            showAsTable: true,
            showAtRoot: false,
            category: 'web',
            icon: 'heroicon-o-document',
            templates: [
                'blog',
            ],
            defaultTemplate: 'blog',
            fieldGroups: [
                'page_banner',
                'featured_posts',
            ],
            inheritance: [], // ['general-page-banner'],
            allowed: [
                'post-page',
            ],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'post-page',
            showAsTable: false,
            showAtRoot: false,
            category: 'web',
            icon: 'heroicon-o-document',
            templates: [
                'post',
            ],
            defaultTemplate: 'post',
            fieldGroups: [
                'page_banner',
                'post_content',
            ],
            inheritance: [], // ['general-page-banner'],
        );

        return $items;
    }

    /**
     * @return ImportDataEntities\Content[]
     */
    protected function getSampleContent()
    {
        $items[] = new ImportDataEntities\Content(
            slug: 'home',
            title: ['en' => 'Home', 'fr' => 'Home'],
            documentType: 'homepage',
            isDefault: true,
            properties: [
                'hero_banner' => [
                    'brief' => [
                        'en' => 'InspireCMS is a newborn CMS. <br/>Clean, simple and fast.',
                        'fr' => 'InspireCMS is a newborn CMS. <br/>Clean, simple and fast.',
                    ],
                    'image_slider' => $this->getRandomMediaAssetInPropertyData(3, 'png'),
                ],
            ],
            publishState: 'publish'
        );
        $items[] = new ImportDataEntities\Content(
            slug: 'about-me',
            title: ['en' => 'About Me', 'fr' => 'About Me'],
            documentType: 'general-page',
            properties: [
                'page_banner' => [
                    'title' => [
                        'en' => 'Lorem ipsum dolor sit amet',
                        'fr' => 'Lorem ipsum dolor sit amet',
                    ],
                    'description' => [
                        'en' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                        'fr' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                    ],
                ],
            ],
            publishState: 'publish',
            parent: 'home',
            template: 'about-us',
        );
        $items[] = new ImportDataEntities\Content(
            slug: 'contact-us',
            title: ['en' => 'Contact Us', 'fr' => 'Contactez-nous'],
            documentType: 'general-page',
            properties: [
                'page_banner' => [
                    'title' => [
                        'en' => 'Contact Us',
                        'fr' => 'Contactez-nous',
                    ],
                    'description' => [
                        'en' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                        'fr' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                    ],
                ],
            ],
            publishState: 'publish',
            parent: 'home',
            template: 'contact-us',
        );
        $items[] = new ImportDataEntities\Content(
            slug: 'site-settings',
            title: ['en' => 'Site Settings', 'fr' => 'Site Settings'],
            documentType: 'settings',
            properties: [
                'social_media' => [
                    'facebook' => 'https://facebook.com',
                    'twitter' => 'https://twitter.com',
                    'linkedin' => 'https://linkedin.com',
                    'instagram' => 'https://instagram.com',
                ],
                'contact_infomation' => [
                    'phone' => '+1 (123) 456-7890',
                    'email' => 'hello@example.com',
                ],
            ],
            publishState: 'publish',
            parent: 'home',
            sitemap: ['enable' => false],
        );
        $items[] = new ImportDataEntities\Content(
            slug: 'blog',
            title: ['en' => 'Blog', 'fr' => 'Blog'],
            documentType: 'blog-page',
            properties: [
                'page_banner' => [
                    'title' => [
                        'en' => 'Blog',
                        'fr' => 'Blog',
                    ],
                    'description' => [
                        'en' => 'Welcome to my blog! Here, I share my thoughts, experiences, and insights on various topics that interest me.',
                        'fr' => 'Bienvenue sur mon blog ! Ici, je partage mes réflexions, expériences et idées sur divers sujets qui m’intéressent.',
                    ],
                ],
                'featured_posts' => [],
            ],
            publishState: 'publish',
            parent: 'home',
        );
        foreach (range(1, 10) as $i) {

            $sampleCatOrTags = [
                'Technology',
                'Travel',
                'Food',
                'Lifestyle',
                'Health',
                'Fitness',
                'Photography',
                'Education',
                'Business',
                'Fashion',
                'DIY',
                'Parenting',
                'Gardening',
                'Finance',
                'Mental Health',
                'Art',
                'Music',
                'Science',
                'Culture',
                'Sports',
                'Nature',
                'History',
                'Writing',
                'Marketing',
                'Productivity',
                'Meditation',
                'Home Decor',
                'Personal Development',
                'Social Media',
                'Sustainability',
            ];

            $items[] = new ImportDataEntities\Content(
                slug: "post-$i",
                title: ['en' => "Post $i", 'fr' => "Post $i"],
                documentType: 'post-page',
                properties: [
                    'page_banner' => [
                        'title' => [
                            'en' => fake()->sentence(5),
                            'fr' => fake()->sentence(5),
                        ],
                        'image' => $this->getRandomMediaAssetInPropertyData(1, 'png'),
                        'description' => [
                            'en' => fake()->sentence(10),
                            'fr' => fake()->sentence(10),
                        ],
                    ],
                    'post_content' => [
                        'categories' => collect($sampleCatOrTags)->random(2)->values()->all(),
                        'tags' => collect($sampleCatOrTags)->random(3)->values()->all(),
                        'content' => [
                            'en' => $this->generateFakeHtmlParagraph(),
                            'fr' => $this->generateFakeHtmlParagraph(),
                        ],
                    ],
                ],
                publishState: 'publish',
                parent: 'home/blog', // content's slug
            );
        }

        return $items;
    }

    protected function addSampleContent(): void
    {
        foreach ($this->getSampleContent() as $item) {
            $this->importDataService->addContent($item);
        }
    }

    protected function addSampleNavigation(): void
    {
        $mainNav = [
            [
                'title' => ['en' => 'Home', 'fr' => 'Accueil'],
                'contentSlugPath' => 'home',
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'About', 'fr' => 'À propos'],
                'contentSlugPath' => 'home/about-me',
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'Blog', 'fr' => 'Blog'],
                'contentSlugPath' => 'home/blog',
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'Contact', 'fr' => 'Contact'],
                'contentSlugPath' => 'home/contact-us',
                'type' => 'content',
            ],
        ];
        $footerNav = [
            [
                'title' => ['en' => 'About', 'fr' => 'À propos'],
                'type' => 'group',
                'children' => [
                    [
                        'title' => ['en' => 'About', 'fr' => 'À propos'],
                        'type' => 'content',
                        'contentSlugPath' => 'home/about-me',
                    ],
                ],
            ],
            [
                'title' => ['en' => 'Resources', 'fr' => 'Ressources'],
                'type' => 'group',
                'children' => [
                    [
                        'title' => ['en' => 'Blog', 'fr' => 'Blog'],
                        'type' => 'content',
                        'contentSlugPath' => 'home/blog',
                    ],
                ],
            ],
            [
                'title' => ['en' => 'Contact', 'fr' => 'Contact'],
                'type' => 'group',
                'children' => [
                    [
                        'title' => ['en' => 'Contact', 'fr' => 'Contact'],
                        'type' => 'content',
                        'contentSlugPath' => 'home/contact-us',
                    ],
                ],
            ],
        ];
        $tempId = 0;
        foreach ($mainNav as $data) {
            $this->importDataService->addNavigation(ImportDataEntities\Navigation::fromArray(array_merge($data, [
                'category' => 'main',
                'id' => $tempId += 1,
            ])));
        }
        foreach ($footerNav as $data) {
            $this->importDataService->addNavigation(ImportDataEntities\Navigation::fromArray(array_merge($data, [
                'category' => 'footer',
                'id' => $tempId += 1,
            ])));
        }
    }

    protected function makeSampleMedia(): void
    {
        $mediaAssetModel = InspireCmsConfig::getMediaAssetModelClass();
        $mediaModel = config('media-library.media_model', \Spatie\MediaLibrary\MediaCollections\Models\Media::class);

        if (! $this->isTableExists($mediaAssetModel) || ! $this->isTableExists($mediaModel)) {
            return;
        }

        // image
        foreach (range(1, 3) as $i) {

            try {

                $fakeName = "image-{$i}.png";

                /** @var MediaAsset */
                $mediaAsset = $mediaAssetModel::create([
                    'title' => $fakeName,
                    'is_folder' => false,
                ]);

                [$base64, $mime] = $this->generateBase64Image(150, 150, $fakeName);

                $mediaAsset
                    ->addMediaFromBase64(
                        $base64,
                        ['mime_type' => $mime, 'name' => $fakeName]
                    )
                    ->usingFileName($fakeName)
                    ->toMediaCollection();

            } catch (\Throwable $th) {
                //
            }

            if (isset($mediaAsset) && ! is_null($mediaAsset)) {
                $this->mediaAssets[] = $mediaAsset;
            }
        }
        // document

        $fakeName = 'dummy.txt';

        /** @var MediaAsset */
        $mediaAsset = $mediaAssetModel::create([
            'title' => $fakeName,
            'is_folder' => false,
        ]);

        $mediaAsset
            ->addMediaFromString('dummy content')
            ->usingFileName($fakeName)
            ->toMediaCollection();

        $this->mediaAssets[] = $mediaAsset;

    }

    protected function makeSampleLanguages(): void
    {
        $items[] = new ImportDataEntities\Language(
            code: 'en',
            isDefault: true,
        );
        $items[] = new ImportDataEntities\Language(
            code: 'fr',
            isDefault: false,
        );

        foreach ($items as $data) {
            $this->importDataService->addLanguage($data);
        }
    }

    protected function isTableExists(string $tableName): bool
    {
        if (! ModelHelper::isTableExists($tableName)) {

            return false;
        }

        return true;
    }

    /**
     * @return (MediaAsset&Model)[]
     */
    protected function getRandomMediaAsset(int $total, $extension = null): array
    {
        $items = collect($this->mediaAssets)
            ->when(
                $extension,
                fn (Collection $collection) => $collection
                    ->where(fn (MediaAsset $asset) => Str::after($asset->title, '.') === $extension)
            )->values()->all();

        if (empty($items)) {
            return [];
        }

        if (count($items) < $total) {
            $tempItems = $items;
            for ($i = 0; $i < $total - count($items); $i++) {
                $randIndexes = array_rand($tempItems, 1);
                $temp = $tempItems[$randIndexes] ?? null;
                if ($temp) {
                    $items[] = $temp;
                }
            }

            return $items;
        }

        $randIndexes = array_rand($items, $total);

        return array_map(fn ($index) => $items[$index], (array) $randIndexes);

    }

    protected function getRandomMediaAssetInPropertyData(int $total, $extension = null): array
    {
        return collect($this->getRandomMediaAsset($total, $extension))
            ->map(fn (MediaAsset $asset) => collect(MediaAssetDto::fromModel($asset)?->toArray() ?? [])
                ->forget('model')
                ->all())
            ->all();
    }

    protected function generateBase64Image($width = 150, $height = 150, $word = null)
    {
        $image = imagecreatetruecolor($width, $height);
        // random color
        [$red, $green, $blue] = [rand(0, 255), rand(0, 255), rand(0, 255)];
        $color = imagecolorallocate($image, $red, $green, $blue);
        imagestring($image, 5, 0, 0, $word ?? 'Sample', $color);
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();

        $base64 = 'data:image/png;base64,' . base64_encode($imageData);

        $mime = 'image/png';

        return [$base64, $mime];
    }

    protected function generateFakeHtmlParagraph(): string
    {
        $paragraph = '';

        // Add random HTML tags
        $tags = ['b', 'i', 'u', 'strong'];
        foreach (explode(' ', fake()->paragraph(5)) as $index => $word) {

            if ($index > 0) {
                $paragraph .= ' ';
            }

            $randomTag = collect($tags)->random();

            if (rand(0, 1)) {
                $word = Str::wrap($word, "<{$randomTag}>", "</{$randomTag}>");
            }

            $paragraph .= $word;
        }

        return '<p>' . $paragraph . '</p>';
    }
}
