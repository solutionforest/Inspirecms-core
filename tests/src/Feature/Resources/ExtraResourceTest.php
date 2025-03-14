<?php

use SolutionForest\InspireCms\Tests\Fixtures\Filament\Resources\PostResource;
use SolutionForest\InspireCms\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->panelPath = '/cms';
    $this->panelId = 'cms';
    $this->createSuperAdminUser();
});

it('can have extra post resource', function () {
    $url = PostResource::getUrl(panel: $this->panelId);

    $this->loginCmsPanelAsSuperAdmin()
        ->get($url)
        ->assertStatus(200);
})->group('resource', 'feature');
