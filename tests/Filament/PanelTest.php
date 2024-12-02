<?php

namespace SolutionForest\InspireCms\Tests\Filament\Resources;

use SolutionForest\InspireCms\Tests\Support\Filament\Resources\PostResource;
use SolutionForest\InspireCms\Tests\TestCase;

class PanelTest extends TestCase
{
    protected $panelPath = '/cms';
    protected $panelId = 'cms';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSuperAdminUser();
    }

    public function test_it_can_access_cms_panel()
    {
        $panel = filament()->getPanel($this->panelId);
        return $this->loginCmsPanelAsSuperAdmin()
            ->get($panel->getUrl())
            ->assertStatus(200);
    }

    public function test_it_can_have_extra_PostResource()
    {
        $url = PostResource::getUrl(panel: $this->panelId);

        return $this->loginCmsPanelAsSuperAdmin()
            ->get($url)
            ->assertStatus(200);
    }
}
