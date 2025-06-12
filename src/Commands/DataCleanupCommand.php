<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use SolutionForest\InspireCms\InspireCmsConfig;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:data-cleanup', description: 'Prune data from prunable models')]
class DataCleanupCommand extends Command
{
    public function handle(): int
    {
        $models = $this->getModels();

        if ($models->isEmpty()) {
            $this->info('No models to cleanup.');

            return static::SUCCESS;
        }

        return $this->call('model:prune', [
            '--model' => $models->toArray(),
        ]);
    }

    protected function getModels()
    {
        $prunable = array_keys(InspireCmsConfig::get('models.prunable'));

        $models = InspireCmsConfig::get('models.fqcn') ?? [];

        return collect($models)
            ->where(fn ($m, $k) => in_array($k, $prunable))
            ->values();
    }
}
