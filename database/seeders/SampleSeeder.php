<?php

namespace SolutionForest\InspireCms\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Helpers\ModelHelper;
use SolutionForest\InspireCms\ImportData\Entities as ImportDataEntities;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Services\ContentServiceInterface;
use SolutionForest\InspireCms\Services\ImportDataServiceInterface;
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
        $this->publishSampleRoutes();

        $this->makeSampleMedia();

        $this->makeSampleLanguages();

        $this->addSampleFields();
        $this->addSampleDocumentTypes();
        $this->addSampleContent();
        $this->addSampleNavigation();
        $this->addSampleTemplates();

        $this->importDataService->run();

        // handle the content have contentPicker field
        if ($blog = $this->contentService->findByRealPath('home/blog')) {
            $availableBlogs = $this->contentService->getUnderRealPath('blogs');
            $blog->propertyData = json_encode([
                'featured_blogs' => [
                    'blogs' => $availableBlogs->random($availableBlogs->count() >= 3 ? 3 : $availableBlogs->count())->map(fn ($item) => $item->getKey())->toArray(),
                ],
            ]);
            $blog->setPublishableState('publish');
            $blog->save();
        }

        /**
         * @var class-string<Model>
         */
        $fieldModel = InspireCmsConfig::getFieldModelClass();
        /**
         * @var class-string<Model>
         */
        $documentTypeModel = InspireCmsConfig::getDocumentTypeModelClass();

        $field = $fieldModel::query()->where('name', 'blogs')->byGroup('featured_blogs')->first();
        if ($field) {
            $field->config = array_merge($field->config ?? [], [
                'documentType' => $documentTypeModel::firstWhere('slug', 'blog')?->getKey(),
            ]);
            $field->save();
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
            $data = new ImportDataEntities\Template(slug: $slug, content: $themedContent);
            $this->importDataService->addTemplate($slug, $data);
        }
    }

    protected function addSampleFields(): void
    {
        $toolbarButtonsForRichEditor = array_keys(\SolutionForest\InspireCms\Fields\Configs\RichEditor::getAllAvailableToolbarButtons());
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'social_media'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'github', type: 'text'),
                new ImportDataEntities\Field(slug: 'twitter', type: 'text'),
                new ImportDataEntities\Field(slug: 'instagram', type: 'text'),
                new ImportDataEntities\Field(slug: 'linkedin', type: 'text'),
                new ImportDataEntities\Field(slug: 'email', type: 'text'),
                new ImportDataEntities\Field(slug: 'facebook', type: 'text'),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'hero_banner'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'brief', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
                new ImportDataEntities\Field(slug: 'image_slider', type: 'mediaPicker', config: ['mimeTypes' => ['image'], 'multiple' => true]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'profile'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'brief', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
                new ImportDataEntities\Field(slug: 'description', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'about_section'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'brief', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
                new ImportDataEntities\Field(slug: 'description', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
                new ImportDataEntities\Field(slug: 'image', type: 'mediaPicker', config: ['mimeTypes' => ['image'], 'multiple' => false]),
                new ImportDataEntities\Field(slug: 'resume', type: 'mediaPicker', config: ['mimeTypes' => ['pdf'], 'multiple' => false]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'page_banner'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'title', type: 'text', config: ['translatable' => true]),
                new ImportDataEntities\Field(slug: 'description', type: 'text', config: ['translatable' => true]),
                new ImportDataEntities\Field(slug: 'image', type: 'mediaPicker', config: ['mimeTypes' => ['image'], 'multiple' => false]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'blog_content'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'categories', type: 'tags', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'tags', type: 'tags', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'content', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
                new ImportDataEntities\Field(slug: 'post_date', type: 'dateTimePicker', config: ['hasTime' => true, 'hasDate' => true, 'displayFormat' => 'Y-m-d H:i:s']),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'contact'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'address', type: 'richEditor', config: ['translatable' => false, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
                new ImportDataEntities\Field(slug: 'phone', type: 'text'),
                new ImportDataEntities\Field(slug: 'email', type: 'text'),
                new ImportDataEntities\Field(slug: 'map', type: 'text'),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'case_content'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'category', type: 'text', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'overview', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
                new ImportDataEntities\Field(slug: 'year', type: 'dateTimePicker', config: ['hasTime' => false, 'hasDate' => true, 'displayFormat' => 'Y']),
                new ImportDataEntities\Field(slug: 'platforms', type: 'tags', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'roles', type: 'tags', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'deliverables', type: 'url', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'content', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'featured_blogs'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'blogs', type: 'contentPicker', config: ['translatable' => false, 'allowedDocumentTypes' => ['blog'], 'multiple' => true]),
            ],
        ];
        foreach ($items as $item) {
            $group = $item['data'];
            $this->importDataService->addFieldGroup($group->slug, $group, $item['fields']);
        }
    }

    protected function addSampleDocumentTypes(): void
    {
        foreach ($this->getSampleDocumentTypes() as $item) {
            $this->importDataService->addDocumentType($item->slug, $item);
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
            category: 'web',
            fieldGroups: [
                'hero_banner',
                'profile',
            ],
            templates: ['home'],
            defaultTemplate: 'home',
            inheritance: [], // ['general-page-banner'],
            icon: 'heroicon-o-home',
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'about',
            showAsTable: false,
            category: 'web',
            fieldGroups: [
                'about_section',
            ],
            templates: ['about'],
            defaultTemplate: 'about',
            inheritance: [], // ['general-page-banner'],
            icon: 'heroicon-o-information-circle',
            rejected: ['homepage'],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'blogs',
            showAsTable: false,
            category: 'web',
            fieldGroups: ['featured_blogs'],
            templates: ['blogs'],
            defaultTemplate: 'blogs',
            inheritance: [], // ['general-page-banner'],
            icon: 'heroicon-o-newspaper',
            rejected: ['homepage'],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'contact-us',
            showAsTable: false,
            category: 'web',
            fieldGroups: [
                'page_banner',
                'contact',
            ],
            templates: ['contact'],
            defaultTemplate: 'contact',
            icon: 'heroicon-o-question-mark-circle',
            rejected: ['homepage'],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'case-studies',
            showAsTable: true,
            category: 'web',
            fieldGroups: [
                'page_banner',
            ],
            templates: ['case-studies'],
            defaultTemplate: 'case-studies',
            icon: 'heroicon-o-clipboard-document-list',
            rejected: ['homepage'],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'case-study',
            showAsTable: false,
            category: 'web',
            fieldGroups: [
                'page_banner',
                'case_content',
            ],
            templates: ['case-study'],
            defaultTemplate: 'case-study',
            icon: 'heroicon-o-clipboard-document-check',
            rejected: ['homepage'],
        );

        $items[] = new ImportDataEntities\DocumentType(
            slug: 'config',
            showAsTable: false,
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
            category: 'data',
            fieldGroups: [],
            templates: [],
            defaultTemplate: null,
            inheritance: [], // ['general-page-banner'],
            icon: 'heroicon-o-newspaper',
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'blog',
            showAsTable: false,
            category: 'data',
            fieldGroups: [
                'page_banner',
                'social_media',
                'blog_content',
            ],
            templates: [
                'blog-featured-item',
                'blog-card-item',
                'blog-grid-item',
                'blog-page',
            ],
            defaultTemplate: 'blog-page',
            icon: 'heroicon-o-newspaper',
            rejected: ['homepage'],
        );

        foreach ($items as &$item) {
            switch ($item->slug) {
                case 'blog-management':
                    $item->rejected = collect($items)->map(fn ($item) => $item->slug)->filter(fn ($slug) => $slug !== 'blog')->toArray();

                    break;
                case 'blog':
                case 'config':
                    $item->rejected = collect($items)->map(fn ($item) => $item->slug)->toArray();

                    break;
                default:
                    $item->rejected = array_unique(array_merge($item->rejected, ['blog']));

                    break;
            }
        }
        return $items;
    }

    protected function addSampleContent(): void
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
                    'image_slider' => $this->getRandomMediaAssetKeys(3, 'png'),
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
            slug: 'blogs',
            title: ['en' => 'Blog Management', 'fr' => 'Gestion des blogs'],
            documentType: 'blog-management',
            properties: [],
            publishState: 'publish',
            parent: null,
            sitemap: ['enable' => false],
        );
        $items[] = new ImportDataEntities\Content(
            slug: 'config',
            title: ['en' => 'Config', 'fr' => 'Config'],
            documentType: 'config',
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
            documentType: 'about',
            properties: [
                'about_section' => [
                    'brief' => [
                        'en' => "<p>I'm Manifest</p><p>Full-time UI/UX designer</p><p>Head of Design at VeronaLabs.com</p>",
                        'fr' => '<p>Je suis Manifest</p><p>Designer UI/UX à plein temps</p><p>Responsable du design chez VeronaLabs.com</p>',
                    ],
                    'image' => Arr::first($this->getRandomMediaAssetKeys(1, 'png')),
                    'description' => [
                        'en' => '<p>I was born in January 1990. After getting my Degree in computer science in 2002, I persuaded my higher study in Human Computer Interaction Design. I got my first job as Graphic Designer in the year 2008. After getting experience in graphic for a year, I moved to UI-UX Designing.</p><p>In 2010, I decided to work as a Freelance Web, UI-UX & Mobile Interface Designer. I find myself still in the learning phase and have strong desire to achieve as many skills as I can.</p>',
                        'fr' => '<p>Je suis né en janvier 1990. Après avoir obtenu mon diplôme en informatique en 2002, j’ai poursuivi mes études supérieures en conception d’interaction homme-machine. J’ai obtenu mon premier emploi en tant que graphiste en 2008. Après avoir acquis de l’expérience en graphisme pendant un an, je suis passé à la conception UI-UX.</p><p>En 2010, j’ai décidé de travailler en tant que designer d’interface Web, UI-UX et mobile indépendant. Je me trouve toujours en phase d’apprentissage et j’ai un fort dés ir d’acquérir autant de compétences que possible.</p>',
                    ],
                    'resume' => Arr::first($this->getRandomMediaAssetKeys(1, 'pdf')),
                ],
            ],
            publishState: 'publish',
            parent: 'home',
        );
        $items[] = new ImportDataEntities\Content(
            slug: 'blog',
            title: ['en' => 'Blog', 'fr' => 'Blog'],
            documentType: 'blogs',
            properties: [
                'featured_blogs' => [],
            ],
            publishState: 'publish',
            parent: 'home',
        );
        foreach (range(1, 10) as $i) {

            $items[] = new ImportDataEntities\Content(
                slug: "blog-$i",
                title: ['en' => "Blog $i", 'fr' => "Blog $i"],
                documentType: 'blog',
                properties: [
                    'page_banner' => [
                        'title' => [
                            'en' => fake()->sentence(5),
                            'fr' => fake()->sentence(5),
                        ],
                        'image' => Arr::first($this->getRandomMediaAssetKeys(1, 'png')),
                        'description' => [
                            'en' => fake()->sentence(10),
                            'fr' => fake()->sentence(10),
                        ],
                    ],
                    'blog_content' => [
                        'categories' => ['Technology', 'Interface Design'],
                        'tags' => ['Technology', 'Interface Design', 'Visual Design'],
                        'content' => [
                            'en' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                            'fr' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                        ],
                        'post_date' => fake()->dateTimeThisYear()->format('Y-m-d H:i:s'),
                    ],
                    'social_media' => [
                        'facebook' => 'https://facebook.com',
                        'twitter' => 'https://twitter.com',
                        'linkedin' => 'https://linkedin.com',
                    ],
                ],
                publishState: 'publish',
                parent: 'blogs',
                sitemap: ['enable' => false],
            );
        }

        $items[] = new ImportDataEntities\Content(
            slug: 'contact-us',
            title: ['en' => 'Contact Us', 'fr' => 'Contactez-nous'],
            documentType: 'contact-us',
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
                    'image' => Arr::first($this->getRandomMediaAssetKeys(1, 'png')),
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
            slug: 'case-studies',
            title: ['en' => 'Works', 'fr' => 'Travaux'],
            documentType: 'case-studies',
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
                documentType: 'case-study',
                properties: [
                    'page_banner' => [
                        'title' => [
                            'en' => fake()->sentence(3),
                            'fr' => fake()->sentence(3),
                        ],
                        'image' => Arr::first($this->getRandomMediaAssetKeys(1, 'png')),
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

        foreach ($items as $item) {
            $this->importDataService->addContent($item->slug, $item->parent, $item);
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
        $model = InspireCmsConfig::getMediaAssetModelClass();
        $mediaModel = config('media-library.media_model', \Spatie\MediaLibrary\MediaCollections\Models\Media::class);

        if (! $this->isTableExists($model) || ! $this->isTableExists($mediaModel)) {
            return;
        }

        $totalRetry = 5;

        foreach (range(1, 3) as $i) {

            try {

                $dir = storage_path('app/temp');

                if (! is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                //Retry x-times to create fake image
                $fakeImage = false;
                $retry = 0;

                $fakeImageWord = "image-{$i}";
                while ($fakeImage === false && $retry < $totalRetry) {
                    $fakeImage = fake()->image(dir: $dir, width: 150, height: 150, word: $fakeImageWord, format: 'png');
                    $retry++;
                }

                if (! $fakeImage) {
                    continue;
                }

                $filename = pathinfo($fakeImage, PATHINFO_BASENAME);

                /** @var MediaAsset */
                $mediaAsset = $model::create([
                    'title' => $filename,
                    'is_folder' => false,
                ]);

                $mediaAsset->addMedia($fakeImage)->toMediaCollection();

            } catch (\Throwable $th) {
                //
            }

            $this->mediaAssets[] = $mediaAsset;
        }

        /** @var MediaAsset */
        $mediaAsset = $model::create([
            'title' => 'dummy.pdf',
            'is_folder' => false,
        ]);

        $mediaAsset->addMedia(\Illuminate\Http\UploadedFile::fake()->create(name: 'dummy.pdf', mimeType: 'application/pdf'))->toMediaCollection();
        $this->mediaAssets[] = $mediaAsset;
    }

    protected function makeSampleLanguages(): void
    {
        $model = InspireCmsConfig::getLanguageModelClass();

        if (! $this->isTableExists($model)) {
            return;
        }

        $languagesData = [
            'en' => [
                'is_default' => true,
            ],
            'fr' => [
                'is_default' => false,
            ],
        ];

        foreach ($languagesData as $code => $data) {

            $this->language[$code] = $model::firstOrCreate(['code' => $code], $data);

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

    protected function getRandomMediaAssetKeys(int $total, $extension = null): array
    {
        return collect($this->getRandomMediaAsset($total, $extension))
            ->map(fn (MediaAsset $asset) => $asset->getKey())
            ->all();
    }

    protected function publishSampleRoutes(): void
    {
        $web = base_path('routes/web.php');
        if (! Str::contains(file_get_contents($web), '\Illuminate\Support\Facades\Route::name(\'sample_content\')')) {
            $routes = <<<PHP

// Sample InspireCMS routes
\Illuminate\Support\Facades\Route::name('sample_content')->middleware(\SolutionForest\InspireCms\InspireCmsConfig::get('content.middlewares'))->get('blog/{slug}', function (\$slug) {
    \$content = inspirecms_content()->getUnderRealPath('blogs')->firstWhere('slug', \$slug);
    if (is_null(\$content) || ! \$content->isPublished()) {
        abort(404);
    }
    /** @var \SolutionForest\InspireCms\Dtos\ContentDto */
    \$dto = \$content->toDto();

    return \$dto->getTemplate('blog-page')->render([
        'content' => \$dto,
    ]);
});

PHP;
            (new Filesystem)->append($web, $routes);
        }
    }
}
