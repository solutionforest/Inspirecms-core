<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use SolutionForest\InspireCms\Facades\ModelManifest;
use SolutionForest\InspireCms\Models\Contracts\Content;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:refresh-indexes', description: 'Refresh indexes (e.g. Content)')]
class RefreshIndexes extends Command
{
    public function handle(): int
    {
        $models = $this->getModels();

        if (empty($models)) {
            $this->error('No models found.');

            return static::FAILURE;
        }

        Artisan::call('scout:sync-index-settings', [], $this->getOutput());

        $bar = $this->output->createProgressBar(count($models));
        $barMsgStyle = 'info';
        $bar->setFormat("<$barMsgStyle>%message%</$barMsgStyle>\n %current%/%max% [%bar%] %percent:3s%%");

        foreach ($models as $model) {

            $bar->setMessage("Importing {$model} ...");

            Artisan::call('scout:import', [
                'model' => $model,
            ], $this->getOutput());

            $bar->advance();

        }

        $bar->finish();

        return static::SUCCESS;
    }

    protected function getModels()
    {
        return array_filter([
            ModelManifest::get(Content::class),
        ], fn ($model) => ! is_null($model) && class_exists($model));
    }
}
