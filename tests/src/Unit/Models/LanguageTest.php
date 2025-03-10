<?php

use SolutionForest\InspireCms\Tests\Models\Content;
use SolutionForest\InspireCms\Tests\Models\ContentRoute;
use SolutionForest\InspireCms\Tests\Models\Language;
use SolutionForest\InspireCms\Tests\TestCase;

use function Pest\Laravel\json;

uses(TestCase::class);

describe('language model', function () {

    it('can change default langugae', function () {
        $defaultLanguage = Language::factory()->create(['code' => 'en', 'is_default' => true]);

        expect($defaultLanguage->is_default)->toBeTrue();

        $newDefaultLanguage = Language::factory()->create(['code' => 'fr', 'is_default' => true]);

        expect($defaultLanguage->fresh()->is_default)->toBeFalse();
        expect($newDefaultLanguage->fresh()->is_default)->toBeTrue();

    });
    
    it('can delete related content routes', function () {
        $en = Language::factory()->create(['code' => 'en', 'is_default' => true]);
        $fr = Language::factory()->create(['code' => 'fr', 'is_default' => false]);

        $content = Content::factory()
            ->has(ContentRoute::factory()->state([
                'language_id' => $fr->getKey(), 
                'uri' => 'abc',
            ]), 'routes')
            ->create();
            
        expect($content->routes->count())->toBe(1);

        $fr->delete();

        $content->refresh();

        expect($content->routes->count())->toBe(0);
    });
    
    it('throws exception for default language when deleting', function () {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete default language');
    
        $language = Language::factory()->create(['is_default' => true]);
        $language->delete();
    });
    
})->group('unit', 'model');