<?php

namespace SolutionForest\InspireCms\Tests\ContentVersions\Commands;

use SolutionForest\InspireCms\Commands\CleanupContentVersion;
use SolutionForest\InspireCms\Tests\TestCase;
// use RefreshDatabase;

class CleanCommandTest extends TestCase
{
    public function test_handle_no_versions_to_cleanup()
    {
        $modelClass = $this->getModel('contentVersion');

        $model = $modelClass::factory()->avoidToClean(false)->create();

        // Run the command
        $this->artisan('inspirecms:cleanup-content-version')
            ->expectsOutput('No content versions to cleanup.')
            ->assertExitCode(CleanupContentVersion::SUCCESS);
    }

    public function test_handle_cleanup_versions()
    {
        // Create some versions to cleanup
        $modelClass = $this->getModel('contentVersion');

        $model = new $modelClass;

        $this->travel(-31)->days();
        $oldVersion = $modelClass::factory()->avoidToClean(false)->create();
        $this->travelBack();

        $this->travel(-29)->days();
        $newVersion = $modelClass::factory()->avoidToClean(false)->create();
        $this->travelBack();

        // Run the command
        $this->artisan('inspirecms:cleanup-content-version')
            ->expectsOutput('Content versions cleaned up.')
            ->assertExitCode(CleanupContentVersion::SUCCESS);

        // Assert the old version is deleted and the new one is not
        $this->assertDatabaseMissing($model->getTable(), ['id' => $oldVersion->id]);
        $this->assertDatabaseHas($model->getTable(), ['id' => $newVersion->id]);
    }

    public function test_handle_cleanup_versions_with_avoid_to_clean()
    {
        // Create some versions to cleanup
        $modelClass = $this->getModel('contentVersion');

        $model = new $modelClass;

        $this->travel(-31)->days();
        $oldVersion = $modelClass::factory()->avoidToClean(true)->create();
        $this->travelBack();

        $this->travel(-29)->days();
        $newVersion = $modelClass::factory()->avoidToClean(false)->create();
        $this->travelBack();

        // Run the command
        $this->artisan('inspirecms:cleanup-content-version')
            ->expectsOutput('No content versions to cleanup.')
            ->assertExitCode(CleanupContentVersion::SUCCESS);

        // Assert the old version is deleted and the new one is not
        $this->assertDatabaseHas($model->getTable(), ['id' => $oldVersion->id]);
        $this->assertDatabaseHas($model->getTable(), ['id' => $newVersion->id]);
    }

    public function test_handle_cleanup_versions_with_publish_log()
    {
        // Create some versions to cleanup
        $modelClass = $this->getModel('contentVersion');
        $publishVerionModelClass = $this->getModel('ContentPublishVersion');

        $model = new $modelClass;
        $publishVersionModel = new $publishVerionModelClass;

        $this->travel(-31)->days();
        $oldVersion = $modelClass::factory()->avoidToClean(false)->withPublishLog()->create();
        $this->travelBack();

        $this->travel(-29)->days();
        $newVersion = $modelClass::factory()->avoidToClean(false)->withPublishLog()->create();
        $this->travelBack();

        // Run the command
        $this->artisan('inspirecms:cleanup-content-version')
            ->expectsOutput('Content versions cleaned up.')
            ->assertExitCode(CleanupContentVersion::SUCCESS);

        // Assert the old version is deleted and the new one is not
        $this->assertDatabaseMissing($model->getTable(), ['id' => $oldVersion->id]);
        $this->assertDatabaseHas($model->getTable(), ['id' => $newVersion->id]);

        $this->assertDatabaseMissing($publishVersionModel->getTable(), ['version_id' => $oldVersion->id]);
        $this->assertDatabaseHas($publishVersionModel->getTable(), ['version_id' => $newVersion->id]);
    }
}