<?php

use Illuminate\Console\Command;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\Tests\Models\ContentPublishVersion;
use SolutionForest\InspireCms\Tests\Models\ContentVersion;
use SolutionForest\InspireCms\Tests\Models\Import;
use SolutionForest\InspireCms\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->command = 'inspirecms:data-cleanup';
});

it('has no jobs to cleanup', function () {
    $this->artisan($this->command)->assertExitCode(Command::SUCCESS);
});

describe('import', function () {

    it('cleans up import jobs', function () {
        // Create some import jobs that can be cleared
        $jobs = Import::factory()->count(5)->isCompleted()->create([
            'created_at' => now()->subDays(ImportDataHelper::retrieveClearanceDaysInterval() + 1),
        ]);

        $this->assertDatabaseCount(app(Import::class), count($jobs));

        $this->artisan($this->command)->assertExitCode(Command::SUCCESS);

        // Assert that the jobs were deleted
        $this->assertDatabaseCount(app(Import::class), 0);
    });

})->group('feature', 'command', 'import');

describe('content version', function () {

    it('has no versions to cleanup', function () {
        $model = ContentVersion::factory()->avoidToClean(false)->create();

        // Run the command
        $this->artisan($this->command)->assertExitCode(Command::SUCCESS);
    });

    it('cleans up versions', function () {
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
        $this->artisan($this->command)->assertExitCode(Command::SUCCESS);

        // Assert the old version is deleted and the new one is not
        $this->assertDatabaseMissing($model, ['id' => $oldVersion->id]);
        $this->assertDatabaseHas($model, ['id' => $newVersion->id]);
    });

    it('cleans up versions with avoid to clean', function () {
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
        $this->artisan($this->command)->assertExitCode(Command::SUCCESS);

        // Assert the old version is deleted and the new one is not
        $this->assertDatabaseHas($model, ['id' => $oldVersion->id]);
        $this->assertDatabaseHas($model, ['id' => $newVersion->id]);
    });

    it('cleans up versions with publish log', function () {
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
        $this->artisan($this->command)->assertExitCode(Command::SUCCESS);

        // Assert the old version is deleted and the new one is not
        $this->assertDatabaseMissing($model, ['id' => $oldVersionId]);
        $this->assertDatabaseHas($model, ['id' => $newVersionId]);

        $oldVersionExists = $publishVersionModel::withoutGlobalScopes([])->where('version_id', $oldVersionId)->exists();
        $newVersionExists = $publishVersionModel::withoutGlobalScopes([])->where('version_id', $newVersionId)->exists();

        expect($oldVersionExists)->toBeFalse();
        expect($newVersionExists)->toBeTrue();
    });

})->group('feature', 'command', 'content-version');
