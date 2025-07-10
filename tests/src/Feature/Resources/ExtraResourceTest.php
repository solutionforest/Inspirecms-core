<?php

use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Tests\Fixtures\Filament\Resources\PostResource;
use SolutionForest\InspireCms\Tests\TestCase;

uses(TestCase::class);
pest()->group('feature', 'fi-resource');

beforeEach(function () {
    $this->panelPath = '/' . InspireCmsConfig::get('admin.path', 'cms');
    $this->panelId = InspireCmsConfig::getPanelId();
    $this->createSuperAdminUser();
});

it('can have extra post resource', function () {
    $url = PostResource::getUrl(panel: $this->panelId);

    $this->loginCmsPanelAsSuperAdmin()
        ->get($url)
        ->assertStatus(200);
});
