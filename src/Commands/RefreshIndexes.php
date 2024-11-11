<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use SolutionForest\InspireCms\Facades\ModelManifest;
use SolutionForest\InspireCms\Models\Contracts\Content;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:refresh-indexes')]
class RefreshIndexes extends Command
{
    public function handle(): int
    {
        $models = $this->getModels();

        if (empty($models)) {
            $this->error('No models found.');

            return static::FAILURE;
        }

        foreach ($models as $model) {
            $this->info("Refreshing indexes for {$model}...");

            $records = $model::all();

            $bar = $this->output->createProgressBar($records->count());

            $records->each(function ($record) use ($bar) {
                $record->searchable();
                $bar->advance();
            });

            $bar->finish();

            $this->info("Indexes refreshed for {$model}.");
        }

        return static::SUCCESS;
    }

    protected function getModels()
    {
        return array_filter([
            ModelManifest::get(Content::class),
        ], fn ($model) => !is_null($model) && class_exists($model));
    }
}
