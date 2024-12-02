<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\ImportJob;
use SolutionForest\InspireCms\Services\ImportJobServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(
    name: 'inspirecms:importjob:execute',
    description: 'Execute pending import jobs.',
)]
class ExecuteImportJob extends Command
{
    protected function configure()
    {
        $this->addOption('limit', 'l', InputArgument::OPTIONAL, 'Limit the number of jobs to execute.', null);
    }

    public function handle(ImportJobServiceInterface $importJobService)
    {
        $records = $this->getJobs();

        if ($records->isEmpty()) {
            $this->info('No pending jobs found.');

            return static::SUCCESS;
        }

        foreach ($records as $record) {
            $this->info("Executing job {$record->getKey()} ...");

            try {
                $importJobService->execute($record);
                $this->info("Job {$record->getKey()} completed successfully.");
            } catch (\Exception $e) {
                $this->error("Job {$record->getKey()} failed: {$e->getMessage()}");
            }
        }
        
        return static::SUCCESS;
    }

    /**
     * @return Collection<ImportJob&Model>
     */
    protected function getJobs()
    {
        $model = InspireCmsConfig::getImportJobModelClass();

        /**
         * @var \Illuminate\Database\Eloquent\Builder<ImportJob&Model>
         */
        $query = $model::query();

        if (($limit = $this->option('limit')) && intval($limit) > 0) {
            $query->limit($limit);
        }

        return $query->wherePending()->get();
    }
}
