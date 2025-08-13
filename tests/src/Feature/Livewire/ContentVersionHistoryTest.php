<?php

use Livewire\Livewire;
use Mockery\MockInterface;
use SolutionForest\InspireCms\Licensing\LicenseManager;
use SolutionForest\InspireCms\Tests\TestCase;

uses(TestCase::class);
pest()->group('livewire', 'feature', 'content-version-history');

function getLivewireParams($content): array
{
    return [
        'ownerRecord' => $content,
        'pageClass' => 'SolutionForest\\InspireCms\\Filament\\Resources\\ContentResource\\Pages\\EditContentRecord',
    ];
}

it('renders_content_version_history_component', function () {
    $content = $this->createCmsContent(
        propData: [
            'banner' => [
                'title' => 'Test Content Title',
            ],
        ],
        publishState: 'publish'
    );
    Livewire::test('inspirecms::content-version-history', getLivewireParams($content))
        ->assertSee(__('inspirecms::resources/content-version.tables.search_placeholder'));
});

test('rollback_content_version', function () {

    // Guard against running this test without a super admin user
    $this->createSuperAdminUser();
    $this->loginCmsPanelAsSuperAdmin();

    $content = $this->createCmsContent(
        propData: ['banner' => ['title' => 'Test Content Title']],
        publishableData: ['published_at' => now()->subDays(2)],
        publishState: 'publish'
    );

    expect($content->contentVersions()->count())->toBe(1);
    expect($content->toDto()->getPropertyValue('banner', 'title'))->toBe('Test Content Title');

    // Add new version
    $content = $this->addCmsContentVersion($content, ['banner' => ['title' => 'Test Content Title 2']], ['published_at' => now()], 'publish');
    expect($content->contentVersions()->count())->toBe(2);
    expect($content->toDto()->getPropertyValue('banner', 'title'))->toBe('Test Content Title 2');

    $contentVersions = $content->contentVersions()->get();
    $firstVersion = $contentVersions->first();
    $latestVersion = $contentVersions->last();
    expect($firstVersion)->not->toBeNull();
    expect($latestVersion)->not->toBeNull();
    expect($firstVersion->id != $latestVersion->id)->toBeTrue();

    Livewire::test('inspirecms::content-version-history', getLivewireParams($content))
        ->assertCountTableRecords(2)
        ->assertTableActionDisabled('rollbackToVersion', $firstVersion);

    $this->mock(LicenseManager::class, function (MockInterface $mock) {
        $mock->shouldReceive('canRollbackVersion')->andReturn(true);
    });
    expect(app(LicenseManager::class)->canRollbackVersion())->toBe(true);

    Livewire::test('inspirecms::content-version-history', getLivewireParams($content))
        ->assertTableActionDisabled('rollbackToVersion', $latestVersion)
        ->callTableAction('rollbackToVersion', $firstVersion)
        ->assertHasNoTableActionErrors();

});
