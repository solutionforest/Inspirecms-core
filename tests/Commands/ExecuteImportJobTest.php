<?php

namespace SolutionForest\InspireCms\Tests\Commands;

use Illuminate\Support\Facades\Storage;
use SolutionForest\InspireCms\Helpers\FileHelper;
use SolutionForest\InspireCms\Tests\TestCase;
use SolutionForest\InspireCms\Tests\TestModels\ImportJob;

class ExecuteImportJobTest extends TestCase
{
    public function test_handle_no_pending_jobs()
    {
        $this->artisan('inspirecms:importjob:execute')
            ->expectsOutput('No pending jobs found.')
            ->assertExitCode(0);
    }

    //todo: fix this test
    // public function test_handle_executes_pending_jobs()
    // {
    //     $job = $this->createImportJobWithFakeFile('test.zip');

    //     $this->artisan('inspirecms:importjob:execute')
    //         ->expectsOutput("Executing job {$job->getKey()} ...")
    //         ->assertExitCode(0);

    //     $job->refresh();

    //     $this->assertTrue($job->finished_at != null);
    // }

    public function test_handle_job_execution_fails()
    {
        $job = $this->createImportJobWithFakeFile('test.csv');

        $this->artisan('inspirecms:importjob:execute')
            ->expectsOutput("Executing job {$job->getKey()} ...")
            ->assertExitCode(0);

        $job->refresh();

        $this->assertTrue($job->failed_at != null);
    }

    protected function createImportJobWithFakeFile($filename)
    {
        $jobDiskName = 'local';
        /** @var Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter */
        $jobDisk = Storage::disk($jobDiskName);

        if (pathinfo($filename, PATHINFO_EXTENSION) == 'zip') {

            $folderName = pathinfo($filename, PATHINFO_FILENAME);

            $fakeFiles = [
                'Views' => [
                    'fake.blade.php',
                ],
                'DocumentTypes' => [
                    'fake.json',
                ],
            ];

            $jobDisk->makeDirectory($folderName, 0777, true);

            foreach ($fakeFiles as $folder => $files) {

                foreach ($files as $file) {

                    $fileFolder = $folderName . DIRECTORY_SEPARATOR . $folder;

                    // Create directory with permissions
                    if (! $jobDisk->directoryExists($fileFolder)) {
                        $jobDisk->makeDirectory($fileFolder, 0777, true);
                    }

                    $dumpContent = pathinfo($file, PATHINFO_EXTENSION) == 'json' ? json_encode(['test' => 'test']) : '<div>test</div>';

                    $jobDisk->put($fileFolder . DIRECTORY_SEPARATOR . $file, $dumpContent);

                }
            }

            $zipPath = $jobDisk->path($filename);

            FileHelper::buildZipFromFolder($jobDisk->path($folderName), $zipPath);

            // Remove temp folder and zip
            $jobDisk->deleteDirectory($folderName);
        }

        $job = ImportJob::factory()->create([
            'file' => $filename,
            'disk' => $jobDiskName,
        ]);

        $job->refresh();

        return $job;
    }
}
