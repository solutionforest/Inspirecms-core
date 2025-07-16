<?php

use Livewire\Livewire;
use Mockery\MockInterface;
use SolutionForest\InspireCms\Licensing\LicenseManager;
use SolutionForest\InspireCms\Tests\Models\Content;
use SolutionForest\InspireCms\Tests\Models\DocumentType;
use SolutionForest\InspireCms\Tests\Models\Field;
use SolutionForest\InspireCms\Tests\Models\FieldGroup;
use SolutionForest\InspireCms\Tests\TestCase;

uses(TestCase::class);
pest()->group('livewire', 'feature', 'content-version-history');

function createContent(array $data = [], array $publishableData = [], ?string $publishState = 'publish'): Content
{
    $facDocumentType = DocumentType::factory(['category' => 'web']);

    foreach ($data as $key => $value) {
        if (! is_array($value)) {
            continue;
        }
        $facFieldGroup = FieldGroup::factory(['name' => $key]);
        foreach (array_keys($value) as $fieldKey) {
            $facFieldGroup = $facFieldGroup->has(Field::factory(['name' => $fieldKey, 'type' => 'text']));
        }
        $facDocumentType = $facDocumentType->has($facFieldGroup);
    }

    $facContent = Content::factory()->for($facDocumentType);
    if (! empty($data)) {
        $facContent = $facContent->havePropertyData($data);
    }

    $content = $facContent->make();
    if ($publishState) {
        $content->setPublishableState($publishState);
    }
    if (! empty($publishableData)) {
        $content->setPublishableData($publishableData);
    }
    $content->save();
    $content->refresh();

    return $content;
}

function addContentVersion(Content $content, array $data = [], array $publishableData = [], ?string $publishState = 'publish'): Content
{
    if (! empty($data)) {
        $content->propertyData = json_encode($data);
    }
    if ($publishState) {
        $content->setPublishableState($publishState);
    }
    if (! empty($publishableData)) {
        $content->setPublishableData($publishableData);
    }
    $content->save();
    $content->refresh();

    return $content;
}

function getLivewireParams($content): array
{
    return [
        'ownerRecord' => $content,
        'pageClass' => 'SolutionForest\\InspireCms\\Filament\\Resources\\ContentResource\\Pages\\EditContentRecord',
    ];
}

it('renders_content_version_history_component', function () {
    $content = createContent(
        [
            'banner' => [
                'title' => 'Test Content Title',
            ],
        ],
    );
    Livewire::test('inspirecms::content-version-history', getLivewireParams($content))
        ->assertSee(__('inspirecms::resources/content-version.tables.search_placeholder'));
});

test('rollback_content_version', function () {

    // Guard against running this test without a super admin user
    $this->createSuperAdminUser();
    $this->loginCmsPanelAsSuperAdmin();

    $content = createContent(['banner' => ['title' => 'Test Content Title']], ['published_at' => now()->subDays(2)], 'publish');

    expect($content->contentVersions()->count())->toBe(1);
    expect($content->toDto()->getPropertyValue('banner', 'title'))->toBe('Test Content Title');

    // Add new version
    $content = addContentVersion($content, ['banner' => ['title' => 'Test Content Title 2']], ['published_at' => now()], 'publish');
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
        ->assertTableActionHidden('rollbackToVersion', $firstVersion);

    $this->mock(LicenseManager::class, function (MockInterface $mock) {
        $mock->shouldReceive('canRollbackVersion')->andReturn(true);
    });
    expect(app(LicenseManager::class)->canRollbackVersion())->toBe(true);

    Livewire::test('inspirecms::content-version-history', getLivewireParams($content))
        ->assertTableActionHidden('rollbackToVersion', $latestVersion)
        ->callTableAction('rollbackToVersion', $firstVersion)
        ->assertHasNoTableActionErrors();

});
