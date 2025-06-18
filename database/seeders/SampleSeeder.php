<?php

namespace SolutionForest\InspireCms\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Helpers\ModelHelper;
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

        // update config of contentPicker field for featured_blogs
        if (($dtBlogData = InspireCmsConfig::getDocumentTypeModelClass()::firstWhere('slug', 'blog-data'))
            && ($fFeaturedBlogs = collect($this->getSampleFields())->first(fn (ImportDataEntities\FieldGroup $v) => $v->slug === 'featured_blogs'))
        ) {
            $fFeaturedBlogs->fields = collect($fFeaturedBlogs->fields)
                ->map(function (ImportDataEntities\Field $field) use ($dtBlogData) {
                    if ($field->slug === 'blogs') {
                        $field->config = array_merge($field->config ?? [], [
                            'documentType' => $dtBlogData->getKey(),
                        ]);
                    }

                    return $field;
                })
                ->all();
            $this->importDataService->addFieldGroup(
                data: $fFeaturedBlogs,
            );
        }

        // handle the content have contentPicker field
        if (
            ($cBlogData = $this->contentService->getUnderRealPath(path: 'blog-management', limit: 10))
            && $cBlogData->isNotEmpty()
            && ($cBlogIndex = collect($this->getSampleContent())->first(fn (ImportDataEntities\Content $v) => $v->slug === 'blogs' && $v->parent === 'parent'))
        ) {
            $cBlogIndex->properties['featured_blogs']['blogs'] = $cBlogData->random(3)->map(fn ($item) => $item->getKey())->all();
            $this->importDataService->addContent(
                data: $cBlogIndex
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
        $themes = ['manifest', 'blogrock', 'know-press'];

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
            slug: 'social_media',
            fields: [
                new ImportDataEntities\Field(slug: 'github', type: 'text'),
                new ImportDataEntities\Field(slug: 'twitter', type: 'text'),
                new ImportDataEntities\Field(slug: 'instagram', type: 'text'),
                new ImportDataEntities\Field(slug: 'linkedin', type: 'text'),
                new ImportDataEntities\Field(slug: 'email', type: 'text'),
                new ImportDataEntities\Field(slug: 'facebook', type: 'text'),
            ]
        );
        $items[] = new ImportDataEntities\FieldGroup(
            slug: 'hero_banner',
            fields: [
                new ImportDataEntities\Field(slug: 'brief', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor, ...$extraConfigForRichEditor]),
                new ImportDataEntities\Field(slug: 'image_slider', type: 'mediaPicker', config: ['types' => ['image']]),
            ],
        );
        $items[] = new ImportDataEntities\FieldGroup(
            slug: 'profile',
            fields: [
                new ImportDataEntities\Field(slug: 'brief', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor, ...$extraConfigForRichEditor]),
                new ImportDataEntities\Field(slug: 'description', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor, ...$extraConfigForRichEditor]),
            ],
        );
        $items[] = new ImportDataEntities\FieldGroup(
            slug: 'about_section',
            fields: [
                new ImportDataEntities\Field(slug: 'brief', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor, ...$extraConfigForRichEditor]),
                new ImportDataEntities\Field(slug: 'description', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor, ...$extraConfigForRichEditor]),
                new ImportDataEntities\Field(slug: 'image', type: 'mediaPicker', config: ['types' => ['image'], 'max' => 1]),
                new ImportDataEntities\Field(slug: 'resume', type: 'mediaPicker', config: ['types' => ['pdf'], 'max' => 1]),
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
                new ImportDataEntities\Field(slug: 'content', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor, ...$extraConfigForRichEditor]),
            ],
        );
        $items[] = new ImportDataEntities\FieldGroup(
            slug: 'contact',
            fields: [
                new ImportDataEntities\Field(slug: 'address', type: 'richEditor', config: ['translatable' => false, 'toolbarButtons' => $toolbarButtonsForRichEditor, ...$extraConfigForRichEditor]),
                new ImportDataEntities\Field(slug: 'phone', type: 'text'),
                new ImportDataEntities\Field(slug: 'email', type: 'text'),
                new ImportDataEntities\Field(slug: 'map', type: 'text'),
            ],
        );
        $items[] = new ImportDataEntities\FieldGroup(
            slug: 'case_content',
            fields: [
                new ImportDataEntities\Field(slug: 'category', type: 'text', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'overview', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor, ...$extraConfigForRichEditor]),
                new ImportDataEntities\Field(slug: 'year', type: 'dateTimePicker', config: ['hasTime' => false, 'hasDate' => true, 'displayFormat' => 'Y']),
                new ImportDataEntities\Field(slug: 'platforms', type: 'tags', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'roles', type: 'tags', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'deliverables', type: 'url', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'content', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor, ...$extraConfigForRichEditor]),
            ],
        );
        $items[] = new ImportDataEntities\FieldGroup(
            slug: 'featured_blogs',
            fields: [
                new ImportDataEntities\Field(slug: 'blogs', type: 'contentPicker', config: ['translatable' => false, 'documentType' => 'blog']),
            ],
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
            fieldGroups: [
                'hero_banner',
                'profile',
            ],
            templates: ['index'],
            defaultTemplate: 'index',
            inheritance: [], // ['general-page-banner'],
            icon: 'heroicon-o-home',
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'about-us-page',
            showAsTable: false,
            showAtRoot: true,
            category: 'web',
            fieldGroups: [
                'about_section',
            ],
            templates: ['about-us'],
            defaultTemplate: 'about-us',
            inheritance: [], // ['general-page-banner'],
            icon: 'heroicon-o-information-circle',
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'blog-page',
            showAsTable: false,
            showAtRoot: true,
            category: 'web',
            fieldGroups: ['featured_blogs'],
            templates: ['blog'],
            defaultTemplate: 'blog',
            inheritance: [], // ['general-page-banner'],
            icon: 'heroicon-o-document',
            allowed: ['post-page'],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'post-page',
            showAsTable: false,
            showAtRoot: true,
            category: 'web',
            fieldGroups: [],
            templates: [
                'post',
            ],
            defaultTemplate: 'post',
            icon: 'heroicon-o-document',
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'contact-us-page',
            showAsTable: false,
            showAtRoot: true,
            category: 'web',
            fieldGroups: [
                'page_banner',
                'contact',
            ],
            templates: ['contact-us'],
            defaultTemplate: 'contact-us',
            icon: 'heroicon-o-question-mark-circle',
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'case-study-index-page',
            showAsTable: true,
            showAtRoot: true,
            category: 'web',
            fieldGroups: [
                'page_banner',
            ],
            templates: ['case-study-index'],
            defaultTemplate: 'case-study-index',
            icon: 'heroicon-o-document',
            allowed: ['case-study-detail-page'],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'case-study-detail-page',
            showAsTable: false,
            showAtRoot: false,
            category: 'web',
            fieldGroups: [
                'page_banner',
                'case_content',
            ],
            templates: ['case-study-detail'],
            defaultTemplate: 'case-study-detail',
            icon: 'heroicon-o-document',
        );

        $items[] = new ImportDataEntities\DocumentType(
            slug: 'config',
            showAsTable: false,
            showAtRoot: true,
            category: 'data',
            fieldGroups: [
                'social_media',
            ],
            templates: [],
            defaultTemplate: null,
            inheritance: [], // ['general-page-banner'],
            icon: 'heroicon-o-cog-6-tooth',
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'blog-management',
            showAsTable: true,
            showAtRoot: true,
            category: 'data',
            fieldGroups: [],
            templates: [],
            defaultTemplate: null,
            inheritance: [], // ['general-page-banner'],
            icon: 'heroicon-o-folder',
            allowed: ['blog-data'],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'blog-data',
            showAsTable: false,
            showAtRoot: false,
            category: 'data',
            fieldGroups: [
                'page_banner',
                'social_media',
                'post_content',
            ],
            templates: [
                'blog-featured-item',
                'blog-card-item',
                'blog-grid-item',
            ],
            defaultTemplate: null,
            icon: 'heroicon-o-newspaper',
        );

        $allSlugs = collect($items)->map(fn ($item) => $item->slug)->values()->toArray();

        foreach ($items as &$item) {
            switch ($item->slug) {
                case 'homepage':
                    $item->allowed = collect($item->allowed)
                        ->merge($allSlugs)
                        ->unique()
                        ->filter()
                        ->where(fn ($slug) => ! in_array($slug, [
                            'homepage', // self
                            'case-study',   // children under case-study index page
                            'post', // data-type
                        ]))
                        ->values()
                        ->toArray();

                    break;
            }
        }

        return $items;
    }

    /**
     * @return ImportDataEntities\Content[]
     */
    protected function getSampleContent()
    {
        $items[] = new ImportDataEntities\Content(
            slug: 'home',
            title: ['en' => 'Homepage', 'fr' => 'Page d\'accueil'],
            documentType: 'homepage',
            isDefault: true,
            properties: [
                'hero_banner' => [
                    'brief' => [
                        'en' => 'Manifest is a newborn theme. <br/> Clean, simple and fast.',
                        'fr' => 'Manifest est un thème nouveau-né. <br/> Propre, simple et rapide.',
                    ],
                    'image_slider' => $this->getRandomMediaAssetInPropertyData(3, 'png'),
                ],
                'profile' => [
                    'brief' => [
                        'en' => 'Full-time UI/UX designer <br/> Head of Design at VeronaLabs.com',
                        'fr' => 'Designer UI/UX à plein temps <br/> Responsable du design chez VeronaLabs.com',
                    ],
                    'description' => [
                        'en' => '<p>We work with clients around the world from our headquarters in Charlotte, South Carolina</p><p>We focus on naming, branding, brand innovation, mobility design and development, and brand experiences.</p>',
                        'fr' => '<p>Nous travaillons avec des clients du monde entier depuis notre siège social à Charlotte, en Caroline du Sud</p><p>Nous nous concentrons sur la dénomination, le branding, l’innovation de la marque, la conception et le développement de la mobilité, et les expériences de marque.</p>',
                    ],
                ],
            ],
            publishState: 'publish'
        );
        $items[] = new ImportDataEntities\Content(
            slug: 'blog-management',
            title: ['en' => 'Blog Management', 'fr' => 'Gestion des blogs'],
            documentType: 'blog-management', // data-type
            properties: [],
            publishState: 'publish',
            parent: null,
            sitemap: ['enable' => false],
        );
        $items[] = new ImportDataEntities\Content(
            slug: 'config',
            title: ['en' => 'Config', 'fr' => 'Config'],
            documentType: 'config', // data-type
            properties: [
                'social_media' => [
                    'facebook' => 'https://facebook.com',
                    'twitter' => 'https://twitter.com',
                    'linkedin' => 'https://linkedin.com',
                    'instagram' => 'https://instagram.com',
                ],
            ],
            publishState: 'publish',
            parent: null,
            sitemap: ['enable' => false],
        );

        $items[] = new ImportDataEntities\Content(
            slug: 'about',
            title: ['en' => 'About', 'fr' => 'À propos'],
            documentType: 'about-us-page',
            properties: [
                'about_section' => [
                    'brief' => [
                        'en' => "<p>I'm Manifest</p><p>Full-time UI/UX designer</p><p>Head of Design at VeronaLabs.com</p>",
                        'fr' => '<p>Je suis Manifest</p><p>Designer UI/UX à plein temps</p><p>Responsable du design chez VeronaLabs.com</p>',
                    ],
                    'image' => Arr::first($this->getRandomMediaAssetInPropertyData(1, 'png')),
                    'description' => [
                        'en' => '<p>I was born in January 1990. After getting my Degree in computer science in 2002, I persuaded my higher study in Human Computer Interaction Design. I got my first job as Graphic Designer in the year 2008. After getting experience in graphic for a year, I moved to UI-UX Designing.</p><p>In 2010, I decided to work as a Freelance Web, UI-UX & Mobile Interface Designer. I find myself still in the learning phase and have strong desire to achieve as many skills as I can.</p>',
                        'fr' => '<p>Je suis né en janvier 1990. Après avoir obtenu mon diplôme en informatique en 2002, j’ai poursuivi mes études supérieures en conception d’interaction homme-machine. J’ai obtenu mon premier emploi en tant que graphiste en 2008. Après avoir acquis de l’expérience en graphisme pendant un an, je suis passé à la conception UI-UX.</p><p>En 2010, j’ai décidé de travailler en tant que designer d’interface Web, UI-UX et mobile indépendant. Je me trouve toujours en phase d’apprentissage et j’ai un fort dés ir d’acquérir autant de compétences que possible.</p>',
                    ],
                    'resume' => Arr::first($this->getRandomMediaAssetInPropertyData(1, 'txt')),
                ],
            ],
            publishState: 'publish',
            parent: 'home',
        );
        $items[] = new ImportDataEntities\Content(
            slug: 'blog',
            title: ['en' => 'Blog', 'fr' => 'Blog'],
            documentType: 'blog-page',
            properties: [
                'featured_blogs' => [],
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
                documentType: 'blog-data', // data-type
                properties: [
                    'page_banner' => [
                        'title' => [
                            'en' => fake()->sentence(5),
                            'fr' => fake()->sentence(5),
                        ],
                        'image' => Arr::first($this->getRandomMediaAssetInPropertyData(1, 'png')),
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
                    'social_media' => [
                        'facebook' => 'https://facebook.com',
                        'twitter' => 'https://twitter.com',
                        'linkedin' => 'https://linkedin.com',
                    ],
                ],
                publishState: 'publish',
                parent: 'blog-management', // content's slug
                sitemap: ['enable' => false],
            );
        }

        $items[] = new ImportDataEntities\Content(
            slug: 'contact-us',
            title: ['en' => 'Contact Us', 'fr' => 'Contactez-nous'],
            documentType: 'contact-us-page',
            properties: [
                'page_banner' => [
                    'title' => [
                        'en' => 'Contact Us',
                        'fr' => 'Contactez-nous',
                    ],
                    'description' => [
                        'en' => 'If you need our help with your user account, have questions about how to use the platform or are experiencing technical difficulties, please do not hesitate to contact us.',
                        'fr' => 'Si vous avez besoin de notre aide pour votre compte utilisateur, si vous avez des questions sur l’utilisation de la plateforme ou si vous rencontrez des difficultés techniques, n’hésitez pas à nous contacter.',
                    ],
                    'image' => Arr::first($this->getRandomMediaAssetInPropertyData(1, 'png')),
                ],
                'contact' => [
                    'email' => 'example@example.com',
                    'phone' => '+1234567890',
                    'address' => '<p>486 Rahi street, Berlin .98</p><p>Germany</p>',
                ],
            ],
            publishState: 'publish',
            parent: 'home',
        );

        $items[] = new ImportDataEntities\Content(
            slug: 'dynamic-post-page',
            title: ['en' => 'Dynamic Post Page', 'fr' => 'Dynamic Post Page'],
            documentType: 'post-page',
            publishState: 'publish',
            parent: 'home',
            routes: [
                [
                    'locale' => null,
                    'uri' => 'blog/post/{slug}',
                    'is_default_pattern' => false,
                ],
                [
                    'locale' => 'en',
                    'uri' => 'en/blog/post/{slug}',
                    'is_default_pattern' => false,
                ],
                [
                    'locale' => 'fr',
                    'uri' => 'fr/blog/post/{slug}',
                    'is_default_pattern' => false,
                ],
            ],
            webSetting: [
                'seo' => [
                    'meta_title' => 'Post',
                    'og_title' => 'Post',
                ],
            ],
        );

        $items[] = new ImportDataEntities\Content(
            slug: 'case-studies',
            title: ['en' => 'Works', 'fr' => 'Travaux'],
            documentType: 'case-study-index-page',
            properties: [
                'page_banner' => [
                    'title' => [
                        'en' => 'Works',
                        'fr' => 'Travaux',
                    ],
                    'description' => [
                        'en' => 'If you need our help with your user account, have questions about how to use the platform or are experiencing technical difficulties, please do not hesitate to contact us.',
                        'fr' => 'Si vous avez besoin de notre aide pour votre compte utilisateur, si vous avez des questions sur l’utilisation de la plateforme ou si vous rencontrez des difficultés techniques, n’hésitez pas à nous contacter.',
                    ],
                ],
            ],
            publishState: 'publish',
            parent: 'home',
        );
        foreach (range(1, 3) as $i) {
            $caseTitle = fake()->sentence(3);
            $content = collect(range(1, 3))->map(fn () => '<section class="research"><h3>User Research</h3><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p></section>')->implode('');
            $items[] = new ImportDataEntities\Content(
                slug: "case-$i",
                title: ['en' => $caseTitle, 'fr' => $caseTitle],
                documentType: 'case-study-detail-page',
                properties: [
                    'page_banner' => [
                        'title' => [
                            'en' => fake()->sentence(3),
                            'fr' => fake()->sentence(3),
                        ],
                        'image' => Arr::first($this->getRandomMediaAssetInPropertyData(1, 'png')),
                    ],
                    'case_content' => [
                        'category' => 'Product Design',
                        'overview' => [
                            'en' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                            'fr' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                        ],
                        'year' => fake()->year(),
                        'platforms' => ['Web', 'Mobile'],
                        'roles' => ['UI/UX Designer', 'Frontend Developer'],
                        'deliverables' => 'https://example.com',
                        'content' => [
                            'en' => $content,
                            'fr' => $content,
                        ],
                    ],
                ],
                publishState: 'publish',
                parent: 'home/case-studies',
                sitemap: ['enable' => false],
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
                'contentSlugPath' => 'home/about',
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'Works', 'fr' => 'Travaux'],
                'contentSlugPath' => 'home/case-studies',
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
                        'contentSlugPath' => 'home/about',
                    ],
                ],
            ],
            [
                'title' => ['en' => 'Resources', 'fr' => 'Ressources'],
                'type' => 'group',
                'children' => [
                    [
                        'title' => ['en' => 'Works', 'fr' => 'Travaux'],
                        'type' => 'content',
                        'contentSlugPath' => 'home/case-studies',
                    ],
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
