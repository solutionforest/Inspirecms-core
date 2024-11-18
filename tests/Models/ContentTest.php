<?php

namespace SolutionForest\InspireCms\Tests\Models;

use SolutionForest\InspireCms\Facades\ContentStatusManifest;
use SolutionForest\InspireCms\Tests\TestCase;
use SolutionForest\InspireCms\Tests\TestModels\Content;

class ContentTest extends TestCase
{
    /** @test */
    public function it_creates_content_version_and_nestable_tree_after_content_creation()
    {
        // Arrange
        $contentData = [
            'title' => 'Test Content',
            'slug' => 'test-content',
            'status' => ContentStatusManifest::getDefaultValue(),
            'document_type_id' => 1,
            'parent_id' => null,
        ];

        // Act
        $content = new Content($contentData);
        $content->propertyData = ['test' => 'test'];
        $content->save();

        $contentKey = $content->getKey();

        // Assert
        $this->assertDatabaseHas('content', ['id' => $contentKey]);
        $this->assertDatabaseHas('content_versions', ['content_id' => $contentKey]);
        $this->assertDatabaseHas('nestable_trees', ['nestable_id' => $contentKey, 'nestable_type' => $content->getMorphClass()]);
    }

    /** @test */
    public function test_create_publish_content_version()
    {
        $status = ContentStatusManifest::getOption('publish');

        // Arrange
        $contentData = [
            'title' => 'Test Content',
            'slug' => 'test-content',
            'document_type_id' => 1,
            'parent_id' => null,
        ];

        $publishTime = now();
        // Act
        /**
         * @var Content
         */
        $content = new Content($contentData);
        $content->status = $status->getValue();
        $content->propertyData = ['test' => 'test'];
        $content->setPublishableData([
            'published_at' => $publishTime,
        ]);
        $content->setPublishableState($status->getName());
        $content->save();

        $contentKey = $content->getKey();

        // Assert
        $this->assertDatabaseHas('content', ['id' => $contentKey]);
        $this->assertDatabaseHas('content_versions', ['content_id' => $contentKey]);
        $this->assertDatabaseHas('content_publish_version', ['content_id' => $contentKey]);
    }

    /** @test */
    public function delete_children_if_parent_is_deleted()
    {
        // Arrange
        $parent = Content::factory()->create();
        $child = Content::factory()->create(['parent_id' => $parent->id]);

        // Act
        $parent->delete();

        // Assert
        $this->assertSoftDeleted('content', ['id' => $child->id]);
    }

    /** @test */
    public function restore_parent_if_child_is_restored()
    {
        // Arrange
        $parent = Content::factory()->create();
        $child = Content::factory()->create(['parent_id' => $parent->id]);
        $child->delete();

        // Act
        $child->restore();

        // Assert
        $this->assertDatabaseHas('content', ['id' => $parent->id]);
    }

    /** @test */
    public function delete_content_versions_and_nestable_tree_if_content_is_deleted()
    {
        // Arrange
        $content = Content::factory()->create();

        // Assert 1
        $this->assertDatabaseHas('content', ['id' => $content->id]);
        $this->assertDatabaseHas('content_versions', ['content_id' => $content->id]);
        $this->assertDatabaseHas('nestable_trees', ['nestable_id' => $content->id, 'nestable_type' => $content->getMorphClass()]);

        // Act
        $content->forceDelete();

        // Assert 2
        $this->assertDatabaseMissing('content', ['id' => $content->id]);
        $this->assertDatabaseMissing('content_versions', ['content_id' => $content->id]);
        $this->assertDatabaseMissing('content_publish_version', ['content_id' => $content->id]);
        $this->assertDatabaseMissing('nestable_trees', ['content_id' => $content->id]);
    }
}
