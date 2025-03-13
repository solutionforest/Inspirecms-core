<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use SolutionForest\InspireCms\Helpers\FileHelper;
use SolutionForest\InspireCms\Tests\Models\Import;
use SolutionForest\InspireCms\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->command = 'inspirecms:import';
});

describe('import command', function () {

    it('handles no pending jobs', function () {
        $this->artisan($this->command)
            ->expectsOutput('No pending jobs found.')
            ->assertExitCode(Command::SUCCESS);
    });

    it('handles job execution failure', function () {
        $job = createImportJobWithFakeFile('test.csv');

        $this->artisan($this->command)
            ->expectsOutput("Executing job {$job->getKey()} ...")
            ->assertExitCode(Command::SUCCESS);

        $job->refresh();

        expect($job->failed_at)->not->toBeNull();
    });

})->group('command', 'feature', 'import');

function createImportJobWithFakeFile($filename)
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

    $job = Import::factory()->create([
        'file_name' => $filename,
        'file_disk' => $jobDiskName,
    ]);

    $job->refresh();

    return $job;
}
