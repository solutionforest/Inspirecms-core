<?php

namespace SolutionForest\InspireCms\Tests\Models;

use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Tests\TestCase;
use SolutionForest\InspireCms\Tests\TestModels\Content;
use SolutionForest\InspireCms\Tests\TestModels\DocumentType;

class DocumentTypeTest extends TestCase
{
    /** @test */
    public function test_deleting_document_type_with_content_throws_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete this document type because it has content.');

        $documentType = DocumentType::factory()->create();

        $content = new Content([
            'title' => ['en' => 1],
            'slug' => '1',
            'status' => 0,
            'parent_id' => KeyHelper::generateMinUuid(),
            'document_type_id' => $documentType->getKey(),
        ]);
        $content->preloadContentVersionData();
        $content->save();

        $documentType->delete();
    }

    /** @test */
    public function test_deleting_document_type_without_content_does_not_throw_exception()
    {
        $documentType = DocumentType::factory()->create();

        $documentType->delete();

        $this->assertTrue(true); // If no exception is thrown, the test passes
    }
}
