<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'inspirecms:clear-cache',
    description: 'Clear the InspireCMS cache'
)]
class ClearCache extends Command
{
    const CACHE_TYPES = [
        'languages' => 'languages',
        'routes' => 'routes',
        'navigation' => 'navigation',
    ];

    public function __construct()
    {
        parent::__construct();

        // options
        $this->addOption('all', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Clear all caches');
        foreach (static::CACHE_TYPES as $option => $description) {
            $this->addOption($option, null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, "Clear {$description} cache");
        }

    }

    public function handle(): int
    {
        if ($this->option('all')) {
            $this->clearAllCaches();
        } else {
            $this->clearSelectedCaches();
        }

        return static::SUCCESS;
    }

    protected function clearAllCaches(): void
    {
        $this->info('Clearing all caches...');

        foreach (array_keys(static::CACHE_TYPES) as $type) {
            $this->clearCache($type);
        }

        $this->info('All caches cleared successfully!');
    }

    protected function clearSelectedCaches(): void
    {
        $targetTypes = array_filter(array_keys(static::CACHE_TYPES), fn ($option) => $this->option($option));
        if (empty($targetTypes)) {
            $this->error('No cache types selected. Use --all to clear all caches or specify cache types.');

            return;
        }

        $this->info('Clearing selected caches...');
        foreach ($targetTypes as $type) {
            $this->clearCache($type);
        }
        $this->info('Selected caches cleared successfully!');
    }

    protected function clearCache(string $type): void
    {
        if (in_array($type, ['languages', 'routes', 'navigation'])) {
            $this->wrapClearCache($type, function ($type) {

                switch ($type) {
                    case 'languages':
                        inspirecms()->forgetCachedLanguages();

                        break;
                    case 'routes':
                        inspirecms()->forgetCachedContentRoutes();

                        // $this->callSilent('route:clear');
                        break;
                    case 'navigation':
                        inspirecms()->forgetCachedNavigation();

                        break;
                }
            });
        }

    }

    private function wrapClearCache(string $type, callable $callback): void
    {
        $this->comment("-- Clearing {$type} cache...");
        $callback($type);
    }
}
