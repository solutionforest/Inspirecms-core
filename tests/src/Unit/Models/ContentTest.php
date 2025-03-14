<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use SolutionForest\InspireCms\Facades\ContentStatusManifest;
use SolutionForest\InspireCms\Tests\Models\Content;
use SolutionForest\InspireCms\Tests\Models\DocumentType;
use SolutionForest\InspireCms\Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

describe('content model', function () {

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

})->group('unit', 'model');
