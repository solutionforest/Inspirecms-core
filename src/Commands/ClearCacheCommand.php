<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use SolutionForest\InspireCms\Licensing\LicenseManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'inspirecms:clear-cache')]
class ClearCacheCommand extends Command
{
    const CACHE_TYPES = [
        'languages' => 'languages',
        'routes' => 'routes',
        'navigation' => 'navigation',
        'offline_licenses' => 'offline licenses',
    ];

    protected function configure(): void
    {
        $this->addOption('all', null, InputOption::VALUE_NONE, 'Clear all caches');
        foreach (static::CACHE_TYPES as $option => $description) {
            $this->addOption($option, null, InputOption::VALUE_NONE, "Clear {$description} cache");
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
        if (in_array($type, array_keys(static::CACHE_TYPES))) {
            $this->wrapClearCache($type, function ($type) {

                switch ($type) {
                    case 'languages':
                        inspirecms()->forgetCachedLanguages();

                        break;

                    case 'routes':
                        inspirecms()->forgetCachedContentRoutes();

                        break;

                    case 'navigation':
                        inspirecms()->forgetCachedNavigation();

                        break;

                    case 'offline_licenses':
                        app(LicenseManager::class)->optimize();

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
