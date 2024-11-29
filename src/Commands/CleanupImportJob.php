<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\ImportJob;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'inspirecms:importjob:cleanup',
    description: 'Cleanup old and completed import jobs.',
)]
class CleanupImportJob extends Command
{
    public function handle()
    {
        $jobsToCleanup = $this->getImportJobsToCleanup();

        if ($jobsToCleanup->isEmpty()) {
            $this->info('No import jobs to cleanup.');

            return static::SUCCESS;
        }

        $progressBar = $this->output->createProgressBar($jobsToCleanup->count());
        $progressBar->setMessage('Cleaning up import jobs...');
        $progressBar->start();

        foreach ($jobsToCleanup as $job) {
            $job->delete();
            $progressBar->advance();
        }
        $progressBar->finish();

        $this->info('Import jobs cleaned up.');

        return static::SUCCESS;
    }

    /**
     * @return Collection<ImportJob&Model>
     */
    protected function getImportJobsToCleanup()
    {
        $model = InspireCmsConfig::getImportJobModelClass();

        return $model::query()
            ->whereCanClear()
            ->get();
    }
}
