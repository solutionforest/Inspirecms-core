<?php

namespace SolutionForest\InspireCms\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory;
use SolutionForest\InspireCms\Helpers\ModelHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:import-sample-data')]
class ImportSampleData extends Command
{
    protected array $mediaAssets = [];

    protected array $language = [];

    protected array $templates = [];

    protected array $fieldGroups = [];

    protected array $documentTypes = [];

    protected array $content = [];

    public function handle(): int
    {
        // Ensure the default data is imported
        $this->call('inspirecms:import-default-data');

        $this->comment("\nImporting sample data ...");

        $this->call('vendor:publish', [
            '--tag' => 'inspirecms-sample-views',
            '--force' => true,
        ]);
        $this->call('vendor:publish', [
            '--tag' => 'inspirecms-sample-templates',
            '--force' => true,
        ]);

        $this->makeSampleMedia();

        $this->makeSampleLanguages();
        $this->makeSampleTemplates();
        $this->makeSampleFields();
        $this->makeSampleDocumentTypes();

        $this->upsertFieldsAfterDocumentTypes();

        $this->makeSampleContent();
        $this->makeSampleNavigation();

        return static::SUCCESS;
    }

    protected function makeSampleMedia(): void
    {
        $this->comment("\nCreate sample media ...");

        $model = InspireCmsConfig::getMediaAssetModelClass();
        $mediaModel = Media::class;

        if (! $this->isTableExists($model) || ! $this->isTableExists($mediaModel)) {
            return;
        }

        $mediaData = collect(range(1, 5))->map(function () {
            $size = '400x400';
            // Random color
            $backgroundColor = str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
            $foregroundColor = str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);

            $format = 'png';

            // Random text
            $text = str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);

            return "https://dummyimage.com/{$size}/{$backgroundColor}/{$foregroundColor}.{$format}&text={$text}";

        });

        $this->withCustomProgressBar($mediaData, function ($url, $key, $progress) use ($model) {

            $progress->setMessage("Creating media: '$url'");

            /** @var MediaAsset */
            $mediaAsset = $model::create([
                'title' => $url,
                'is_folder' => false,
            ]);
            $mediaAsset->addMediaFromUrl($url)->toMediaCollection();

            $this->mediaAssets[] = $mediaAsset;

        }, 'Creating media ...', 'Media created.');

    }

    protected function makeSampleLanguages(): void
    {
        $this->comment("\nCreate sample languages ...");

        $model = InspireCmsConfig::getLanguageModelClass();

        if (! $this->isTableExists($model)) {
            return;
        }

        $languagesData = [
            'en' => [
                'name' => 'English',
                'is_default' => true,
            ],
            'id' => [
                'name' => 'Indonesian',
                'is_default' => false,
            ],
        ];

        $this->withCustomProgressBar($languagesData, function ($data, $code, $progress) use ($model) {

            $progress->setMessage("Creating language: '$code'");

            $this->language[$code] = $model::firstOrCreate(['code' => $code], $data);

        }, 'Creating languages ...', 'Languages created.');

    }

    protected function makeSampleTemplates(): void
    {
        $this->comment("\nCreate sample templates ...");

        $model = InspireCmsConfig::getTemplateModelClass();

        if (! $this->isTableExists($model)) {
            return;
        }

        $templatesData = [
            'home',
            'about',
            'articles',
            'article',
            'projects',
        ];

        $this->withCustomProgressBar($templatesData, function ($slug, $key, $progress) use ($model) {

            $progress->setMessage("Creating template: '$slug'");

            $this->templates[$slug] = $model::firstOrCreate(['slug' => $slug]);

        }, 'Creating templates ...', 'Templates created.');
    }

    protected function makeSampleFields(): void
    {
        $this->comment("\nCreate sample fields and field groups ...");

        $model = InspireCmsConfig::getFieldGroupModelClass();

        if (! $this->isTableExists($model)) {
            return;
        }

        $fieldGroupsData = [
            'general_page_banner' => [
                'title' => [
                    'type' => 'translate',
                    'config' => [
                        'field' => 'text',
                    ],
                ],
                'description' => [
                    'type' => 'translate',
                    'config' => [
                        'field' => 'text',
                    ],
                ],
                'image' => [
                    'type' => 'mediaPicker',
                    'config' => [
                        'mimeTypes' => ['image'],
                        'multiple' => false,
                    ],
                ],
            ],
            'recently_articles' => [
            ],
            'image_slider' => [
                'image' => [
                    'type' => 'mediaPicker',
                    'config' => [
                        'mimeTypes' => ['image'],
                        'multiple' => true,
                    ],
                ],
            ],
            'social_media' => [
                'github' => [
                    'type' => 'text',
                ],
                'twitter' => [
                    'type' => 'text',
                ],
                'instagram' => [
                    'type' => 'text',
                ],
                'linkedin' => [
                    'type' => 'text',
                ],
                'email' => [
                    'type' => 'text',
                ],
            ],
            'article_detail_content' => [
                'title' => [
                    'type' => 'translate',
                    'config' => [
                        'field' => 'text',
                    ],
                ],
                'content' => [
                    'type' => 'translate',
                    'config' => [
                        'field' => 'richEditor',
                        'config' => [
                            'toolbarButtons' => array_keys(\SolutionForest\InspireCms\Fields\Configs\RichEditor::getAllAvailableToolbarButtons()),
                        ],
                    ],
                ],
                'image' => [
                    'type' => 'mediaPicker',
                    'config' => [
                        'mimeTypes' => ['image'],
                        'multiple' => false,
                    ],
                ],
            ],
            'projects' => [
                'projects' => [
                    'type' => 'repeater',
                    'config' => [
                        'fields' => [
                            [
                                'field' => 'translate',
                                'name' => 'title',
                                'fieldConfig' => [
                                    'field' => 'text',
                                    'config' => [],
                                ],
                            ], [
                                'field' => 'translate',
                                'name' => 'description',
                                'fieldConfig' => [
                                    'field' => 'textArea',
                                    'config' => [],
                                ],
                            ], [
                                'field' => 'text',
                                'name' => 'link',
                            ], [
                                'field' => 'mediaPicker',
                                'name' => 'image',
                                'fieldConfig' => [
                                    'mimeTypes' => ['image'],
                                    'multiple' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->withCustomProgressBar($fieldGroupsData, function ($fields, $name, $progress) use ($model) {

            $data['name'] = $name;
            $data['title'] = (string) str($name)->title()->replace('_', ' ');

            $fieldGroup = $model::firstOrCreate(
                Arr::only($data, ['name']),
                Arr::except($data, ['name'])
            );

            foreach ($fields as $fieldName => $fieldData) {

                $fieldData['name'] = $fieldName;

                if (! isset($fieldGroup['label'])) {
                    $fieldData['label'] = (string) str($fieldName)->title()->replace('_', ' ');
                }

                $field = $fieldGroup->fields()->firstOrCreate(
                    Arr::only($fieldData, ['name']),
                    Arr::except($fieldData, ['name'])
                );

            }

            $this->fieldGroups[$name] = $fieldGroup;

        }, 'Creating field groups ...', 'Field groups created.');

    }

    protected function makeSampleDocumentTypes(): void
    {
        $this->comment("\nCreate sample document types ...");

        $model = InspireCmsConfig::getDocumentTypeModelClass();

        if (! $this->isTableExists($model)) {
            return;
        }

        $documentTypesData = [
            // inheritance
            'general-page-banner' => [
                'field_groups' => [
                    'general_page_banner',
                ],
                'templates' => [],
                'default_template' => null,
                'inheritance' => [],
                'data' => [
                    'show_children_as_table' => false,
                    'category' => DocumentTypeCategory::Inheritance->value,
                    'title' => 'Page Banner',
                ],
            ],

            // web
            'homepage' => [
                'field_groups' => [
                    'image_slider',
                    'recently_articles',
                    'social_media',
                ],
                'templates' => [
                    'home',
                ],
                'default_template' => 'home',
                'inheritance' => ['general-page-banner'],
                'data' => ['category' => 'web'],
            ],
            'about' => [
                'field_groups' => ['article_detail_content'],
                'templates' => [
                    'about',
                ],
                'default_template' => 'about',
                'inheritance' => [],
                'data' => ['category' => 'web'],
                'parent' => 'homepage',
            ],
            'articles' => [
                'field_groups' => [
                    'recently_articles',
                ],
                'templates' => [
                    'articles',
                ],
                'default_template' => 'articles',
                'inheritance' => ['general-page-banner'],
                'data' => [
                    'category' => 'web',
                    'show_children_as_table' => true,
                ],
                'parent' => 'homepage',
            ],
            'article' => [
                'field_groups' => [
                    'article_detail_content',
                ],
                'templates' => [
                    'article',
                ],
                'default_template' => 'article',
                'inheritance' => [],
                'data' => ['category' => 'web'],
                'parent' => 'articles',
            ],
            'projects' => [
                'field_groups' => [
                    'projects',
                ],
                'templates' => [
                    'projects',
                ],
                'default_template' => 'projects',
                'inheritance' => ['general-page-banner'],
                'data' => ['category' => 'web'],
                'parent' => 'homepage',
            ],
        ];

        $this->withCustomProgressBar($documentTypesData, function ($data, $slug, $progress) use ($model) {

            $progress->setMessage("Creating document type: '$slug'");

            $documentTypeData = $data['data'];
            if (! isset($documentTypeData['title'])) {
                $documentTypeData['title'] = (string) str($slug)->title()->replace('-', ' ');
            }

            $documentType = $model::firstOrCreate(['slug' => $slug], $documentTypeData);

            $this->documentTypes[$slug] = $documentType;

            if (isset($data['parent']) && $parentDocumentType = $this->documentTypes[$data['parent']] ?? null) {
                $documentType->parent()->associate($parentDocumentType);
                $documentType->save();
            }

            if (! empty($data['field_groups'])) {
                $fieldGroups = collect($this->fieldGroups)->where(fn ($v, $k) => in_array($k, $data['field_groups']))->map(fn ($v) => $v->getKey())->filter()->values();
                $documentType->fieldGroups()->sync($fieldGroups);
            }

            if (! empty($data['templates'])) {
                $templates = collect($this->templates)->where(fn ($v, $k) => in_array($k, $data['templates']))->map(fn ($v) => $v->getKey())->filter()->values();
                $documentType->templates()->sync($templates);
            }

            if (isset($data['default_template']) && $defaultTemplate = $this->templates[$data['default_template']] ?? null) {
                $documentType->setAsDefaultTemplate($defaultTemplate->getKey());
            }

            foreach ($data['inheritance'] ?? [] as $inheritance) {
                $inheritanceDocumentType = $this->documentTypes[$inheritance] ?? null;
                $documentType->inheritDocumentType($inheritanceDocumentType);
            }

        }, 'Creating document types ...', 'Document types created.');

    }

    protected function upsertFieldsAfterDocumentTypes(): void
    {
        $this->comment("\nCreate fields after document types ...");

        $model = InspireCmsConfig::getFieldGroupModelClass();

        if (! $this->isTableExists($model)) {
            return;
        }

        $fieldGroupData = [
            'recently_articles' => [
                'articles' => [
                    'type' => 'contentPicker',
                    'config' => [
                        'documentType' => $this->documentTypes['article']->getKey(),
                        'multiple' => true,
                    ],
                ],
            ],
        ];

        $this->withCustomProgressBar($fieldGroupData, function ($fields, $name, $progress) use ($model) {

            $fieldGroup = $model::where('name', $name)->first();

            foreach ($fields as $fieldName => $fieldData) {

                $fieldData['name'] = $fieldName;

                if (! isset($fieldGroup['label'])) {
                    $fieldData['label'] = (string) str($fieldName)->title()->replace('_', ' ');
                }

                $field = $fieldGroup->fields()->firstOrCreate(
                    Arr::only($fieldData, ['name']),
                    Arr::except($fieldData, ['name'])
                );

            }

            $this->fieldGroups[$name] = $fieldGroup;

        }, 'Creating fields after document types ...', 'Fields created.');
    }

    protected function makeSampleContent(): void
    {
        $this->comment("\nCreate sample content ...");

        $model = InspireCmsConfig::getContentModelClass();

        if (! $this->isTableExists($model)) {
            return;
        }

        // home
        $home = $this->makeContent([
            'document_type_id' => $this->documentTypes['homepage']->getKey(),
            'title' => ['en' => 'Homepage', 'id' => 'Beranda'],
            'slug' => 'home',
        ]);
        $this->createContentIfNotExists($home);
        $this->content['home'] = $home;

        // about
        $about = $this->makeContent([
            'document_type_id' => $this->documentTypes['about']->getKey(),
            'title' => ['en' => 'About', 'id' => 'Tentang'],
            'slug' => 'about',
            'parent_id' => $home->getKey(),
        ]);
        $this->createContentIfNotExists($about);
        $this->content['about'] = $about;

        // articles
        $articles = $this->makeContent([
            'document_type_id' => $this->documentTypes['articles']->getKey(),
            'title' => ['en' => 'Articles', 'id' => 'Artikel'],
            'slug' => 'articles',
            'parent_id' => $home->getKey(),
        ]);
        $this->createContentIfNotExists($articles);
        $this->content['articles'] = $articles;

        // article
        $articlesArr = [];
        foreach (range(1, 5) as $i) {
            $article = $this->makeContent([
                'document_type_id' => $this->documentTypes['article']->getKey(),
                'title' => ['en' => "Article $i", 'id' => "Artikel $i"],
                'slug' => "article-$i",
                'parent_id' => $articles->getKey(),
            ]);
            $this->createContentIfNotExists($article);
            $this->content["article-$i"] = $article;

            $articlesArr[] = $article;
        }

        // projects
        $projects = $this->makeContent([
            'document_type_id' => $this->documentTypes['projects']->getKey(),
            'title' => ['en' => 'Projects', 'id' => 'Proyek'],
            'slug' => 'projects',
            'parent_id' => $home->getKey(),
        ]);
        $this->createContentIfNotExists($projects);
        $this->content['projects'] = $projects;

        $contentPropertyData = [
            'home' => [
                'image_slider' => [
                    'image' => collect(array_rand($this->mediaAssets, 5))->map(fn ($i) => $this->mediaAssets[$i]?->getKey())->filter()->all(),
                ],
                'recently_articles' => [
                    'articles' => collect(array_rand($articlesArr, 3))->map(fn ($i) => $articlesArr[$i]?->getKey())->filter()->all(),
                ],
                'social_media' => [
                    'github' => 'https://github.com',
                    'twitter' => 'https://twitter.com',
                    'instagram' => 'https://instagram.com',
                    'linkedin' => 'https://linkedin.com',
                    'email' => 'test@example.com',
                ],
                'general_page_banner' => [
                    'title' => ['en' => 'Welcome to our website', 'id' => 'Selamat datang di website kami'],
                    'description' => ['en' => 'We provide the best service for you', 'id' => 'Kami menyediakan layanan terbaik untuk Anda'],
                ],
            ],
            'about' => [
                'article_detail_content' => [
                    'title' => ['en' => 'About Us', 'id' => 'Tentang Kami'],
                    'content' => [
                        'en' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec purus feugiat, molestie ipsum et, consectetur libero. Donec nec est)',
                        'id' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec purus feugiat, molestie ipsum et, consectetur libero. Donec nec est)',
                    ],
                    'image' => $this->mediaAssets[array_rand($this->mediaAssets)]->getKey(),
                ],
            ],
            'articles' => [
                'general_page_banner' => [
                    'title' => ['en' => 'Articles', 'id' => 'Artikel'],
                    'description' => ['en' => 'List of articles', 'id' => 'Daftar artikel'],
                ],
                'recently_articles' => [
                    'articles' => collect(array_rand($articlesArr, 3))->map(fn ($i) => $articlesArr[$i]?->getKey())->filter()->all(),
                ],
            ],
            'projects' => [
                'general_page_banner' => [
                    'title' => ['en' => 'Projects', 'id' => 'Proyek'],
                    'description' => ['en' => 'List of projects', 'id' => 'Daftar proyek'],
                ],
                'projects' => [
                    'projects' => [
                        [
                            'title' => ['en' => 'Project 1', 'id' => 'Proyek 1'],
                            'description' => ['en' => 'Description of project 1', 'id' => 'Deskripsi proyek 1'],
                            'link' => 'https://project1.com',
                            'image' => $this->mediaAssets[array_rand($this->mediaAssets)]->getKey(),
                        ],
                        [
                            'title' => ['en' => 'Project 2', 'id' => 'Proyek 2'],
                            'description' => ['en' => 'Description of project 2', 'id' => 'Deskripsi proyek 2'],
                            'link' => 'https://project2.com',
                            'image' => $this->mediaAssets[array_rand($this->mediaAssets)]->getKey(),
                        ],
                        [
                            'title' => ['en' => 'Project 3', 'id' => 'Proyek 3'],
                            'description' => ['en' => 'Description of project 3', 'id' => 'Deskripsi proyek 3'],
                            'link' => 'https://project3.com',
                            'image' => $this->mediaAssets[array_rand($this->mediaAssets)]->getKey(),
                        ],
                    ],
                ],
            ],
        ];

        foreach ($contentPropertyData as $key => $propertyData) {

            $tmpContentId = data_get($this->content, $key)?->getKey();
            /**
             * @var ?Content
             */
            $tmpContent = $tmpContentId ? $model::find($tmpContentId) : null;
            if (! $tmpContent) {
                continue;
            }

            $tmpContent->propertyData = json_encode($propertyData);
            $tmpContent->setPublishableState('publish');
            $tmpContent->update();
        }

        foreach ($articlesArr as $item) {

            $tmpContentId = $item?->getKey();
            /**
             * @var ?Content
             */
            $tmpContent = $tmpContentId ? $model::find($tmpContentId) : null;
            if (! $tmpContent) {
                continue;
            }

            $tmpContent->propertyData = json_encode([
                'article_detail_content' => [
                    'title' => ['en' => $tmpContent->title, 'id' => $tmpContent->title],
                    'content' => [
                        'en' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec purus feugiat, molestie ipsum et, consectetur libero. Donec nec est)',
                        'id' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec purus feugiat, molestie ipsum et, consectetur libero. Donec nec est)',
                    ],
                ],
            ]);
            $tmpContent->setPublishableState('publish');
            $tmpContent->update();
        }

    }

    protected function makeSampleNavigation(): void
    {
        $this->comment("\nCreate sample navigation ...");

        $model = InspireCmsConfig::getNavigationModelClass();

        if (! $this->isTableExists($model)) {
            return;
        }

        $navigationData = [
            [
                'title' => ['en' => 'About', 'id' => 'Tentang'],
                'content_id' => $this->content['about']->getKey(),
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'Articles', 'id' => 'Artikel'],
                'content_id' => $this->content['articles']->getKey(),
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'Projects', 'id' => 'Proyek'],
                'content_id' => $this->content['projects']->getKey(),
                'type' => 'content',
            ],
        ];

        $this->withCustomProgressBar($navigationData, function ($data, $slug, $progress) use ($model) {

            $progress->setMessage("Creating navigation: '$slug'");

            $model::firstOrCreate(['slug' => $slug, 'category' => 'main'], $data);
            $model::firstOrCreate(['slug' => $slug, 'category' => 'footer'], $data);

        }, 'Creating navigations ...', 'Navigations created.');
    }

    protected function isTableExists(string $tableName): bool
    {
        if (! ModelHelper::isTableExists($tableName)) {
            $this->error("Table $tableName does not exist, please run migration first.");

            return false;
        }

        return true;
    }

    protected function withCustomProgressBar($data, Closure $callback, $startMessage = 'Starting ...', $finishedMessage = 'Finished.'): void
    {
        $total = count($data);

        $progress = $this->output->createProgressBar($total);

        $progress->setFormat('%current%/%max% [%bar%] %percent:3s%% %message%');

        $progress->setMessage($startMessage);

        foreach ($data as $key => $value) {

            $callback($value, $key, $progress);

            $progress->advance();
        }

        $progress->setMessage($finishedMessage);
        $progress->finish();
    }

    protected function makeContent($attributes): Content | Model
    {
        $modelClass = InspireCmsConfig::getContentModelClass();

        $model = new $modelClass($attributes);

        return $model;
    }

    protected function createContentIfNotExists(Content $content, $state = 'draft'): void
    {
        $model = InspireCmsConfig::getContentModelClass();

        if ($model::where('slug', $content->slug)->doesntExist()) {
            $content->setPublishableState($state);
            $content->save();

            $this->createContentRelatedModels($content->getKey());
        }
    }

    protected function createContentRelatedModels($contentId)
    {
        /**
         * @var ?Content
         */
        $content = InspireCmsConfig::getContentModelClass()::find($contentId);

        if ($content) {
            $content->sitemap()->create([
                'change_frequency' => \SolutionForest\InspireCms\Base\Enums\SitemapChangeFrequency::Monthly->value,
                'enable' => true,
                'priority' => 0.5,
            ]);
            $content->webSetting()->create([
                'seo' => [
                    'meta_title' => $content->getTranslations('title'),
                ],
                'robots' => [
                    'index' => true,
                    'follow' => true,
                ],
                'redirect_path' => null,
                'redirect_content_id' => KeyHelper::generateMinUuid(),  // must
            ]);
        }
    }
}
