<?php

use SolutionForest\InspireCms\Tests\Fixtures\Filament\Resources\PostResource\Pages\ListPosts;
use SolutionForest\InspireCms\Tests\TestCase;

uses(TestCase::class);
pest()->group('feature', 'fi-resource');

beforeEach(function () {
    $this->createSuperAdminUser();
});

it('can have extra post resource', function () {
    livewire(ListPosts::class)
        ->assertOk();
});
