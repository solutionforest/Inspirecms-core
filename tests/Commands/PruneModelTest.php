<?php

namespace SolutionForest\InspireCms\Tests\Commands;

use Illuminate\Console\Command;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Tests\TestCase;
use SolutionForest\InspireCms\Tests\TestModels\ContentPublishVersion;
use SolutionForest\InspireCms\Tests\TestModels\ContentVersion;
use SolutionForest\InspireCms\Tests\TestModels\ImportJob;

class PruneModelTest extends TestCase
{
    protected static string $command = 'inspirecms:data-cleanup';

    public function test_no_jobs_to_cleanup()
    {
        $this->artisan(static::$command)->assertExitCode(Command::SUCCESS);
    }

    public function test_cleanup_import_job()
    {
        // Create some import jobs that can be cleared
        $jobs = ImportJob::factory()->count(5)->isCompleted()->create([
            'created_at' => now()->subDays(InspireCmsConfig::get('models.prunable.import_job.interval', 30) + 1),
        ]);

        $this->assertDatabaseCount('import_jobs', count($jobs));

        $this->artisan(static::$command)->assertExitCode(Command::SUCCESS);

        // Assert that the jobs were deleted
        $this->assertDatabaseCount('import_jobs', 0);
    }

    public function test_no_versions_to_cleanup()
    {
        $model = ContentVersion::factory()->avoidToClean(false)->create();

        // Run the command
        $this->artisan(static::$command)->assertExitCode(Command::SUCCESS);
    }

    public function test_cleanup_versions()
    {
        // Create some versions to cleanup
        $modelClass = ContentVersion::class;

        $model = new $modelClass;

        $this->travel(-31)->days();
        $oldVersion = $modelClass::factory()->avoidToClean(false)->create();
        $this->travelBack();

        $this->travel(-29)->days();
        $newVersion = $modelClass::factory()->avoidToClean(false)->create();
        $this->travelBack();

        // Run the command
        $this->artisan(static::$command)->assertExitCode(Command::SUCCESS);

        // Assert the old version is deleted and the new one is not
        $this->assertDatabaseMissing($model, ['id' => $oldVersion->id]);
        $this->assertDatabaseHas($model, ['id' => $newVersion->id]);
    }

    public function test_cleanup_versions_with_avoid_to_clean()
    {
        // Create some versions to cleanup
        $modelClass = ContentVersion::class;

        $model = new $modelClass;

        $this->travel(-31)->days();
        $oldVersion = $modelClass::factory()->avoidToClean(true)->create();
        $this->travelBack();

        $this->travel(-29)->days();
        $newVersion = $modelClass::factory()->avoidToClean(false)->create();
        $this->travelBack();

        // Run the command
        $this->artisan(static::$command)->assertExitCode(Command::SUCCESS);

        // Assert the old version is deleted and the new one is not
        $this->assertDatabaseHas($model, ['id' => $oldVersion->id]);
        $this->assertDatabaseHas($model, ['id' => $newVersion->id]);
    }

    public function test__cleanup_versions_with_publish_log()
    {
        // Create some versions to cleanup
        $modelClass = ContentVersion::class;
        $publishVerionModelClass = ContentPublishVersion::class;

        $model = new $modelClass;
        $publishVersionModel = new $publishVerionModelClass;

        $this->travel(-31)->days();
        $oldVersion = $modelClass::factory()->avoidToClean(false)->withPublishLog()->create();
        $oldVersionId = $oldVersion->id;
        $this->travelBack();

        $this->travel(-29)->days();
        $newVersion = $modelClass::factory()->avoidToClean(false)->withPublishLog()->create();
        $newVersionId = $newVersion->id;
        $this->travelBack();

        // Run the command
        $this->artisan(static::$command)->assertExitCode(Command::SUCCESS);

        // Assert the old version is deleted and the new one is not
        $this->assertDatabaseMissing($model, ['id' => $oldVersionId]);
        $this->assertDatabaseHas($model, ['id' => $newVersionId]);

        $oldVersionExists = $publishVersionModel::withoutGlobalScopes([])->where('version_id', $oldVersionId)->exists();
        $newVersionExists = $publishVersionModel::withoutGlobalScopes([])->where('version_id', $newVersionId)->exists();

        $this->assertFalse($oldVersionExists);
        $this->assertTrue($newVersionExists);
    }
}
