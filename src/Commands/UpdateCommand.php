<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use SolutionForest\InspireCms\Base\Commnads\Concerns\WithPixelArt;
use SolutionForest\InspireCms\InspireCms;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:update')]
class UpdateCommand extends Command
{
    use WithPixelArt;

    private bool $forcePublishMigrations = false;

    private bool $forceRunMigration = false;

    public function handle()
    {
        $this->displayPixelArtBanner('InspireCMS Update');

        $this->updatePlugin(inspirecms()->version());

        // 1) Publish config
        if ($this->confirm('Do you want to publish the configuration files?')) {
            $this->call('vendor:publish', [
                '--tag' => InspireCms::CORE_SLUG . '-config',
                '--force' => true, // Force overwrite existing files
            ]);
            $this->info('Configuration files published.');
        } else {
            $this->warn('Skipping configuration publishing.');
        }

        // 2) Install dependencies
        $this->info('Installing dependencies...');
        $this->call(InstallRequirePacakgesCommand::class);

        // 3) Publish migrations
        if ($this->forcePublishMigrations || $this->confirm('Do you want to publish the migrations?', true)) {
            $this->call('vendor:publish', [
                '--tag' => InspireCms::CORE_SLUG . '-migrations',
                '--force' => $this->forceRunMigration,
            ]);
            $this->info('Migrations published.');
        } else {
            $this->warn('Skipping migration publishing.');
        }

        // 4) Run migrations
        if ($this->forceRunMigration || $this->confirm('Do you want to run the migrations now?', true)) {
            $this->call('migrate', [
                '--force' => $this->forceRunMigration,
            ]);
            $this->info('Migrations completed successfully.');
        } else {
            $this->warn('Skipping migrations. You can run them later with `php artisan migrate`.');
        }

        // 5) Publish cms panel
        $this->info('Publishing CMS panel...');
        $this->call(PublishPanelCommand::class);

        // 6) Import default data
        $this->info('Import default data...');
        $this->call(ImportDefaultDataCommand::class, [
            '--skip-samples' => true, // Skip sample data import
        ]);

        $this->info('InspireCMS update complete!');

        return static::SUCCESS;
    }

    /**
     * @param  ?string  $currentVersion
     */
    private function updatePlugin($currentVersion)
    {
        if (empty($currentVersion)) {
            return;
        }

        if (str_starts_with($currentVersion, 'dev') || ! str_contains($currentVersion, '.')) {
            return;
        }

        // Split the version string into parts
        $versionParts = explode('.', $currentVersion);

        // Check if the version has at least two parts
        if (count($versionParts) < 2) {
            return;
        }

        if ($versionParts[0] === '0' && $versionParts[1] === '0' && $versionParts[2] === '96') {
            $this->forcePublishMigrations = true;
            $this->forceRunMigration = true;
        }
    }
}
