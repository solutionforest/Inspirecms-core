<?php

namespace SolutionForest\InspireCms\Tests\Models;

use SolutionForest\InspireCms\Facades\ContentStatusManifest;
use SolutionForest\InspireCms\Tests\TestCase;
use SolutionForest\InspireCms\Tests\TestModels\Content;

class ContentTest extends TestCase
{
    public function test_creates_content_version_and_nestable_tree_after_content_creation()
    {
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
    }

    public function test_delete_children_if_parent_is_deleted()
    {
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
    }

    public function test_delete_content_versions_and_nestable_tree_if_content_is_deleted()
    {
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
    }
}
