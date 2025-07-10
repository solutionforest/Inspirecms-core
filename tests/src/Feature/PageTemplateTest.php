<?php

use SolutionForest\InspireCms\Facades\ContentStatusManifest;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\Tests\Models\Content;
use SolutionForest\InspireCms\Tests\Models\DocumentType;
use SolutionForest\InspireCms\Tests\Models\Field;
use SolutionForest\InspireCms\Tests\Models\FieldGroup;
use SolutionForest\InspireCms\Tests\Models\KeyValue;
use SolutionForest\InspireCms\Tests\Models\Language;
use SolutionForest\InspireCms\Tests\Models\Template;
use SolutionForest\InspireCms\Tests\TestCase;

uses(TestCase::class);
pest()->group('feature');

beforeEach(function () {
    // Add routes
    \SolutionForest\InspireCms\Facades\InspireCms::routes();

    // Ensure the default language is set
    $defaultLangCode = 'en';
    Language::updateOrCreate(
        ['code' => $defaultLangCode],
        ['is_default' => true]
    );
    // Ensure the theme is set up correctly
    $this->theme = 'default'; // Set the theme to default
    KeyValue::updateOrCreate(
        ['key' => TemplateHelper::getCurrentThemeKey()],
        ['value' => $this->theme]
    );
});

it('renders_page_template_correctly', function () {

    $theme = $this->theme;

    $sampleTemplateContent = <<<'EOL'
<div class="page-template">
    <h1>@property('hero', 'title')</h1>
    <p>{{ $content->getTitle() }}</p>
</div>
EOL;

    // Create a test content record
    $content = Content::factory()
        ->for(
            DocumentType::factory()
                ->hasAttached(
                    Template::factory()
                        ->create([
                            'slug' => 'page', // Ensure the template is named 'page'
                            'content' => [
                                $theme => $sampleTemplateContent,
                            ],
                        ]),
                    ['is_default' => true] // Set as default template
                )
                ->has(
                    FieldGroup::factory()
                        ->has(
                            Field::factory([
                                'name' => 'title',
                                'label' => 'Hero Title',
                                'type' => 'text',
                            ]),
                            'fields'
                        )
                        ->state([
                            'title' => 'Hero',
                            'name' => 'hero',
                        ]),
                    'fieldGroups'
                )
                ->create([
                    'title' => 'Page Document Type',
                    'slug' => 'page-document-type',
                    'category' => 'web', // Ensure it's a web type document
                ])
        )
        ->create([
            'title' => 'Test Page',
            'slug' => 'test-page',
        ]);
    $content->refresh();

    // Create publish version
    $status = ContentStatusManifest::getOption('publish');
    $content->status = $status->getValue();
    $publishTime = now();
    $content->propertyData = json_encode([
        'hero' => [
            'title' => 'Test Hero Title',
        ],
    ]);
    $content->setPublishableData([
        'published_at' => $publishTime,
    ]);
    $content->setPublishableState($status->getName());
    $content->save();

    $content->refresh();

    // Visit the page

    $response = $this->get($content->getUrl());

    // Assert response and content

    $response->assertStatus(200);

    $response->assertSee('Test Hero Title');

    $response->assertSee('Test Page');
});
