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

    public function handle()
    {
        $this->displayPixelArtBanner('InspireCMS Update');

        // 1) Publish config
        if ($this->confirm('Do you want to publish the configuration files?', true)) {
            $this->call('vendor:publish', [
                '--tag' => InspireCms::CORE_SLUG . '-config',
                '--force' => true, // Force overwrite existing files
            ]);
            $this->info('Configuration files published.');
        } else {
            $this->warn('Skipping configuration publishing.');
        }

        // 2) Publish migrations
        if ($this->confirm('Do you want to publish the migrations?', true)) {
            $this->call('vendor:publish', [
                '--tag' => InspireCms::CORE_SLUG . '-migrations',
            ]);
            $this->info('Migrations published.');
        } else {
            $this->warn('Skipping migration publishing.');
        }

        // 3) Install dependencies
        $this->info('Installing dependencies...');
        $this->call(InstallRequirePacakgesCommand::class);

        // 4) Run migrations
        if ($this->confirm('Do you want to run the migrations now?', true)) {
            $this->call('migrate');
            $this->info('Migrations completed successfully.');
        } else {
            $this->warn('Skipping migrations. You can run them later with `php artisan migrate`.');
        }

        // 5) Publish cms panel
        $this->info('Publishing CMS panel...');
        $this->call(PublishPanelCommand::class);

        // 6) Import sample data
        $this->info('Import sample data...');
        $this->call(ImportDefaultDataCommand::class, [
            '--skip-samples' => true,
        ]);

        $this->updatePlugin(inspirecms()->version());

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

    }
}
