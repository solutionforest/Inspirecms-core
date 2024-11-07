<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:cleanup-content-version')]
class CleanupContentVersion extends Command
{
    public function handle(): int
    {
        $versionsToCleanup = $this->getContentVersionsToCleanup();

        if ($versionsToCleanup->isEmpty()) {
            $this->info('No content versions to cleanup.');

            return static::SUCCESS;
        }

        $progressBar = $this->output->createProgressBar($versionsToCleanup->count());
        $progressBar->setMessage('Cleaning up content versions...');
        $progressBar->start();

        foreach ($versionsToCleanup as $version) {
            $version->delete();
            $progressBar->advance();
        }
        $progressBar->finish();

        $this->info('Content versions cleaned up.');

        return static::SUCCESS;
    }

    /**
     * @return Collection
     */
    protected function getContentVersionsToCleanup()
    {
        $model = InspireCmsConfig::getContentVersionModelClass();

        return $model::query()
            ->where('avoid_to_clean', false)
            ->where('created_at', '<', now()->subDays(config('inspirecms.scheduled_tasks.cleanup_content_verion.old_content_version_days', 30)))
            ->get();
    }
}
