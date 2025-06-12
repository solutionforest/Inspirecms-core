<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;
use SolutionForest\InspireCms\Base\Commnads\Concerns\WithPixelArt;
use SolutionForest\InspireCms\InspireCms;
use SolutionForest\InspireCms\Licensing\LicenseManager;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:install')]
class InstallCommand extends Command
{
    use WithPixelArt;

    public function handle()
    {
        $this->displayPixelArtBanner('Welcome to the InspireCMS Installer');

        // 1) Ask for license key
        $license = $this->ask(str_replace(
            ':subscribeUrl',
            app(LicenseManager::class)->getSubscriptionUrl(),
            'Please enter your license key (you can get one at :subscribeUrl):'
        ));
        if (! $license) {
            $this->error('License key is required. Installation aborted.');

            return static::FAILURE;
        }

        // 1a) Append to .env
        $this->appendLicenseToEnv($license);

        // 2) Publish config
        if ($this->confirm('Do you want to publish the configuration files?', false)) {
            $this->call('vendor:publish', [
                '--tag' => InspireCms::CORE_SLUG . '-config',
            ]);
            $this->info('Configuration files published.');
        } else {
            $this->warn('Skipping configuration publishing.');
        }

        // 3) Publish migrations
        if ($this->confirm('Do you want to publish the migrations?', true)) {
            $this->call('vendor:publish', [
                '--tag' => InspireCms::CORE_SLUG . '-migrations',
            ]);
            $this->info('Migrations published.');
        } else {
            $this->warn('Skipping migration publishing.');
        }

        // 4) Install dependencies
        $this->info('Installing dependencies...');
        $this->call(InstallRequirePacakgesCommand::class);

        // 5) Run migrations
        if ($this->confirm('Do you want to run the migrations now?', true)) {
            $this->call('migrate');
            $this->info('Migrations completed successfully.');
        } else {
            $this->warn('Skipping migrations. You can run them later with `php artisan migrate`.');
        }

        // 6) Publish cms panel
        $this->info('Publishing CMS panel...');
        $this->call(PublishPanelCommand::class);

        // 7) Import default data
        $this->info('Import default data...');
        $this->call(ImportDefaultDataCommand::class);

        $this->info('InspireCMS installation complete!');

        return static::SUCCESS;
    }

    private function appendLicenseToEnv($license)
    {
        $envPath = base_path('.env');
        $envKey = 'INSPIRECMS_LICENSE_KEY';
        $entry = "{$envKey}={$license}\n";

        if (! file_exists($envPath)) {
            throw new RuntimeException('.env file does not exist.');
        }

        // If .env already contains envKey, replace it
        $envContent = file_get_contents($envPath);
        if (Str::contains($envContent, $envKey)) {
            $envContent = preg_replace("/^{$envKey}=.*/m", "{$envKey}={$license}", $envContent);
            if (file_put_contents($envPath, $envContent) === false) {
                throw new RuntimeException('Failed to update license in .env');
            }
            $this->info('License key saved.');

            return;
        }

        // If not, append it
        if (file_put_contents($envPath, $entry, FILE_APPEND | LOCK_EX) === false) {
            throw new RuntimeException('Failed to append license to .env');
        }

        // Ensure the .env file is readable by the web server
        if (! is_readable($envPath)) {
            chmod($envPath, 0644);
        }

        $this->info('License key saved.');
    }
}
