<?php

use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Tests\Models\Content;
use SolutionForest\InspireCms\Tests\Models\DocumentType;
use SolutionForest\InspireCms\Tests\TestCase;

uses(TestCase::class);
pest()->group('unit', 'model');

it('throws an exception when deleting a document type with content', function () {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Cannot delete this document type because it has content.');

    $documentType = DocumentType::factory(['category' => 'web'])->create();

    $content = Content::factory([
        'title' => ['en' => 1],
        'slug' => '1',
        'status' => 0,
        'parent_id' => KeyHelper::generateMinUuid(),
        'document_type_id' => $documentType->getKey(),
    ])->create();
    $content->preloadContentVersionData();
    $content->save();

    $documentType->delete();
});

it('does not throw an exception when deleting a document type without content', function () {
    $documentType = DocumentType::factory(['category' => 'web'])->create();

    $documentType->delete();

    expect(true)->toBeTrue(); // If no exception is thrown, the test passes
});

