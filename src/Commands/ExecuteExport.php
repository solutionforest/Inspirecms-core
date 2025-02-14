<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Export;
use SolutionForest\InspireCms\Services\ExportServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(
    name: 'inspirecms:export',
    description: 'Execute pending export jobs.',
)]
class ExecuteExport extends Command
{
    protected function configure()
    {
        $this->addOption('limit', 'l', InputArgument::OPTIONAL, 'Limit the number of jobs to execute.', null);
    }

    public function handle(ExportServiceInterface $exportService)
    {
        $records = $this->getJobs();

        if ($records->isEmpty()) {
            $this->info('No pending export jobs.');

            return static::SUCCESS;
        }

        foreach ($records as $record) {
            $this->info("Executing job {$record->getKey()} ...");

            try {
                $exportService->execute($record);

                $this->info("Job {$record->getKey()} completed successfully.");
            } catch (\Exception $e) {
                $this->error("Job {$record->getKey()} failed: {$e->getMessage()}");
            }
        }

        return static::SUCCESS;
    }

    /**
     * @return Collection<Export&Model>
     */
    protected function getJobs()
    {
        $model = InspireCmsConfig::getExportModelClass();

        /**
         * @var \Illuminate\Database\Eloquent\Builder<Export&Model>
         */
        $query = $model::query();

        if (($limit = $this->option('limit')) && intval($limit) > 0) {
            $query->limit($limit);
        }

        return $query->wherePending()->get();
    }
}
