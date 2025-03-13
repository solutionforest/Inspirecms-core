<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use SolutionForest\InspireCms\Livewire\DocumentTypePaginator;
use SolutionForest\InspireCms\Tests\Models\DocumentType;
use SolutionForest\InspireCms\Tests\TestCase;

uses(TestCase::class);
// uses(RefreshDatabase::class);

describe('document type paginator', function () {
    
    it('can paginate document types', function () {
        DocumentType::factory(['show_at_root' => true])
            ->count(30)
            ->create();

        Livewire::test(DocumentTypePaginator::class)
            ->assertSee('Next');
    });

    it('can search document types', function () {
        DocumentType::factory()->create(['title' => 'Test Document Type', 'show_at_root' => true]);
        DocumentType::factory()->create(['title' => 'Another Type', 'show_at_root' => true]);

        Livewire::test(DocumentTypePaginator::class)
            ->set('search', 'Test')
            ->assertSee('Test Document Type')
            ->assertDontSee('Another Type');
    });

    it('resets page when search is updated', function () {
        
        DocumentType::factory(['show_at_root' => true])
            ->count(30)
            ->create();

        $pageName = DocumentTypePaginator::PAGE_NAME;

        Livewire::test(DocumentTypePaginator::class)
            ->set('paginators', [$pageName => 2])
            ->set('search', 'Test')
            ->assertSet('paginators', [$pageName => 1]);
    });

})->group('feature', 'livewire');