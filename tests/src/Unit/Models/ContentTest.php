<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use SolutionForest\InspireCms\Facades\ContentStatusManifest;
use SolutionForest\InspireCms\Tests\Models\Content;
use SolutionForest\InspireCms\Tests\Models\DocumentType;
use SolutionForest\InspireCms\Tests\TestCase;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

uses(TestCase::class);
uses(RefreshDatabase::class);
pest()->group('unit', 'model');

beforeEach(function () {
    $this->registerCmsRoutes();
    $this->ensureDefaultTheme();
    $this->ensureDefaultLanguage();
});

it('creates content version and nestable tree after content creation', function () {
    // Act
    $content = Content::factory()->havePropertyData([
        'test' => 'test',
    ])->create([
        'title' => 'Test Content',
        'slug' => 'test-content',
    ]);
    $content->refresh();

    $contentKey = $content->getKey();

    // Assert
    $this->assertDatabaseHas('content', ['id' => $contentKey]);
    $this->assertDatabaseHas('content_versions', ['content_id' => $contentKey]);
    $this->assertDatabaseHas('nestable_trees', ['nestable_id' => $contentKey, 'nestable_type' => $content->getMorphClass()]);

    // Create publish version
    $status = ContentStatusManifest::getOption('publish');
    $content->status = $status->getValue();
    $publishTime = now();
    $content->propertyData = json_encode(['test' => 'test2']);
    $content->setPublishableData([
        'published_at' => $publishTime,
    ]);
    $content->setPublishableState($status->getName());
    $content->save();

    $content->refresh();

    $this->assertDatabaseHas('content_publish_version', ['content_id' => $contentKey]);
});

it('create or delete related routes and paths', function () {

    $webTypeDocumentType = DocumentType::factory([
        'category' => 'web',
    ])->create()->refresh();
    $content = Content::factory()->create([
        'slug' => 'test-create-route',
        'document_type_id' => $webTypeDocumentType->getKey(),
    ])->refresh();

    $contentId = $content->getKey();

    // Assert 1
    $this->assertDatabaseHas('content', ['id' => $contentId]);
    $this->assertDatabaseHas('content_paths', ['key' => $contentId]);
    $this->assertDatabaseHas('content_routes', ['content_id' => $contentId]);

    // Act 1: soft delete
    $content->delete();

    // Assert 2
    $this->assertSoftDeleted('content', ['id' => $contentId]);
    $this->assertDatabaseHas('content_paths', ['key' => $contentId]);
    $this->assertDatabaseHas('content_routes', ['content_id' => $contentId]);

    // Act 2: force delete
    $content->refresh()->forceDelete();

    // Assert 3
    $this->assertDatabaseMissing('content', ['id' => $contentId]);
    $this->assertDatabaseMissing('content_paths', ['key' => $contentId]);
    $this->assertDatabaseMissing('content_routes', ['content_id' => $contentId]);
});

it('deletes children if parent is deleted', function () {
    // Arrange
    $parent = Content::factory()->create([
        'slug' => 'test-delete-children',
    ]);
    $parent->refresh();
    $child = Content::factory()->create([
        'slug' => 'test-delete-children-child',
        'parent_id' => $parent->id,
    ]);
    $child->refresh();

    // Act
    $parent->delete();

    // Assert
    $this->assertSoftDeleted('content', ['id' => $child->id]);
});

it('deletes content versions and nestable tree if content is deleted', function () {
    // Arrange
    $content = Content::factory()->havePropertyData([
        'test' => 'test',
    ])->create(['slug' => 'test-force-delete']);
    $content->refresh();

    // Assert 1
    $this->assertDatabaseHas('content', ['id' => $content->id]);
    $this->assertDatabaseHas('content_versions', ['content_id' => $content->id]);
    $this->assertDatabaseHas('nestable_trees', ['nestable_id' => $content->id, 'nestable_type' => $content->getMorphClass()]);

    // Act
    $content->forceDelete();

    // Assert 2
    $this->assertDatabaseMissing('content', ['id' => $content->id]);
    $this->assertDatabaseMissing('content_versions', ['content_id' => $content->id]);
    $this->assertDatabaseMissing('nestable_trees', ['content_id' => $content->id]);
});

test('content is unpublished when latest version is not published', function () {
    $publishableData = [
        'published_at' => now()->subDays(2), // Just ensure the content is published initially
    ];

    $parent = $this->createCmsContent(data: [
        'slug' => 'home',
        'parent_id' => null,
        'is_default' => true,
    ], publishState: 'publish', publishableData: $publishableData);
    $contentData = [
        'slug' => 'test',
        'parent_id' => $parent->getKey(),
        'is_default' => false,
    ];

    $validateContent = function (Content $content, bool $validatePublished) use ($contentData) {
        $contentService = app(\SolutionForest\InspireCms\Services\ContentServiceInterface::class);
        $contentUri = "/{$contentData['slug']}";
        $contentParentPath = 'home';
        $contentRealPath = "{$contentParentPath}/{$contentData['slug']}";
        $strDocumentTypeSlug = $content->documentType?->slug ?? 'default';

        if ($validatePublished) {
            assertTrue($content->isPublished(), 'Content should be published.');
            assertNotNull($content->getPublishTime(), 'Content should have a publish time.');
            assertSame(
                1,
                $contentService->findByIds(ids: $content->getKey(), isPublished: true)->count(),
                'Content should be published and found by service.'
            );
            assertSame(
                1,
                $contentService->findByRoutePatternWithLangId(uri: $contentUri, isDefaultRoutePattern: true, isPublished: true)->count(),
                'Content should be found by route pattern.'
            );
            assertSame(
                1,
                $contentService->findByRealPath(path: $contentRealPath, isPublished: true)->count(),
                'Content should be found by real path.'
            );
            assertSame(
                1,
                $contentService->getUnderRealPath(path: $contentParentPath, isPublished: true)->count(),
                'Content should be found under real path.'
            );
            assertSame(
                1,
                $contentService->getByDocumentType(documentType: $strDocumentTypeSlug, isPublished: true)->count(),
                'Content should be found by document type.'
            );
            $this->get($content->getUrl())->assertOk();
        } else {
            assertFalse($content->isPublished(), 'Content should not be published.');
            assertNull($content->getPublishTime(), 'Content should not have a publish time.');
            assertSame(
                0,
                $contentService->findByIds(ids: $content->getKey(), isPublished: true)->count(),
                'Content should not be published and not found by service.'
            );
            assertSame(
                0,
                $contentService->findByRoutePatternWithLangId(uri: $contentUri, isDefaultRoutePattern: true, isPublished: true)->count(),
                'Content should not be found by route pattern.'
            );
            assertSame(
                0,
                $contentService->findByRealPath(path: $contentRealPath, isPublished: true)->count(),
                'Content should not be found by real path.'
            );
            assertSame(
                0,
                $contentService->getUnderRealPath(path: $contentParentPath, isPublished: true)->count(),
                'Content should not be found under real path.'
            );
            assertSame(
                0,
                $contentService->getByDocumentType(documentType: $strDocumentTypeSlug, isPublished: true)->count(),
                'Content should not be found by document type.'
            );
            $this->get($content->getUrl())->assertNotFound();
        }
    };

    $content = $this->createCmsContent(data: $contentData, publishState: 'publish', publishableData: $publishableData);
    $validateContent($content, true);

    $content = $this->addCmsContentVersion(content: $content, publishState: 'unpublish');
    $validateContent($content, false);

    $content = $this->addCmsContentVersion(content: $content, publishState: 'draft');
    $validateContent($content, false);

    $content = $this->addCmsContentVersion(content: $content, publishState: 'publish', publishableData: $publishableData);
    $validateContent($content, true);
});
