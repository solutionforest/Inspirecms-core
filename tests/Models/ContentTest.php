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

        // Assert
        $this->assertDatabaseHas('content', ['id' => $content->id]);
        $this->assertDatabaseHas('content_versions', ['content_id' => $content->id]);
        $this->assertDatabaseHas('nestable_trees', ['nestable_id' => $content->id, 'nestable_type' => $content->getMorphClass()]);
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

        $content->refresh();

        // Assert
        $this->assertDatabaseHas('content', ['id' => $content->id]);
        $this->assertDatabaseHas('content_versions', ['content_id' => $content->id]);
        $this->assertDatabaseHas('content_publish_version', ['content_id' => $content->id]);
    }
}
