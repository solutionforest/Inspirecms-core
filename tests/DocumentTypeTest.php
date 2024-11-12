<?php

namespace SolutionForest\InspireCms\Tests\Observers;

use SolutionForest\InspireCms\Models\Content;
use SolutionForest\InspireCms\Models\DocumentType;
use SolutionForest\InspireCms\Tests\TestCase;

class DocumentTypeTest extends TestCase
{

    /** @test */
    public function test_deleting_document_type_with_content_throws_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete this document type because it has content.');

        $documentTypeModel = $this->getModel('DocumentType');
        $documentType = $documentTypeModel::factory()->create();

        $this->getModel('Content')::factory(3)->create([
            'document_type_id' => $documentType->getKey()
        ]);

        $documentType->delete();
    }

    /** @test */
    public function test_deleting_document_type_without_content_does_not_throw_exception()
    {
        $documentTypeModel = $this->getModel('DocumentType');

        $documentType = $documentTypeModel::factory()->create();

        $documentType->delete();

        $this->assertTrue(true); // If no exception is thrown, the test passes
    }
}