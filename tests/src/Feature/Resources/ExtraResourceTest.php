<?php

use SolutionForest\InspireCms\Tests\Fixtures\Filament\Resources\PostResource\Pages\ListPosts;
use SolutionForest\InspireCms\Tests\TestCase;

use function Pest\Livewire\livewire;

uses(TestCase::class);
pest()->group('feature', 'fi-resource');

beforeEach(function () {
    $this->createSuperAdminUser();
    $this->loginCmsPanelAsSuperAdmin();
});

it('can have extra post resource', function () {
    livewire(ListPosts::class)
        ->assertOk();
});
