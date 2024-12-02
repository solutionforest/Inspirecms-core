<?php

namespace SolutionForest\InspireCms\Tests\Commands;

use SolutionForest\InspireCms\Commands\CleanupImportJob;
use SolutionForest\InspireCms\Tests\TestCase;
use SolutionForest\InspireCms\Tests\TestModels\ImportJob;

class CleanupImportJobTest extends TestCase
{
    public function test_handle_no_jobs_to_cleanup()
    {
        $this->artisan('inspirecms:importjob:cleanup')
            ->expectsOutput('No import jobs to cleanup.')
            ->assertExitCode(CleanupImportJob::SUCCESS);
    }

    public function test_handle_jobs_to_cleanup()
    {
        // Create some import jobs that can be cleared
        $jobs = ImportJob::factory()->count(5)->isCompleted()->create([
            'created_at' => now()->subDays(config('inspirecms.scheduled_tasks.cleanup_import_job.old_import_job_days', 30) + 1),
        ]);

        $this->assertDatabaseCount('import_jobs', count($jobs));

        $this->artisan('inspirecms:importjob:cleanup')
            ->expectsOutput('Import jobs cleaned up.')
            ->assertExitCode(CleanupImportJob::SUCCESS);

        // Assert that the jobs were deleted
        $this->assertDatabaseCount('import_jobs', 0);
    }
}