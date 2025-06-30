<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:install-require-packages', description: 'Install required packages for InspireCMS')]
class InstallRequirePacakgesCommand extends Command
{
    public function handle(): int
    {
        $this->publishNotificationDataTable();
        $this->installFieldGroupPackage();
        $this->installSpatiePackages();
        $this->installSupportPackage();

        return static::SUCCESS;
    }

    protected function installFieldGroupPackage()
    {
        $this->components->task('Install SolutionForest\\FilamentFieldGroup package', function () {
            // Publish field group package - migration
            Artisan::call('vendor:publish', [
                '--provider' => 'SolutionForest\\FilamentFieldGroup\\FilamentFieldGroupServiceProvider',
                '--tag' => 'filament-field-group-migrations',
            ]);

        });
    }

    protected function installSpatiePackages()
    {
        // Has own custom migration for Spatie\\LaravelPermission package and Spatie\\MediaLibrary package
        $this->components->task('Install Spatie\\LaravelPermission package', function () {

            // Publish Spatie\\LaravelPermission package - migration
            Artisan::call('vendor:publish', [
                '--provider' => 'Spatie\\Permission\\PermissionServiceProvider',
                '--tag' => 'permission-config',
            ]);

        });
    }

    protected function installSupportPackage()
    {
        $this->components->task('Install SolutionForest\\InspireCms\\Support package', function () {
            Artisan::call('vendor:publish', [
                '--provider' => 'SolutionForest\\InspireCms\\Support\\InspireCmsSupportServiceProvider',
                '--tag' => 'inspirecms-support-migrations',
            ]);
        });
    }

    protected function publishNotificationDataTable()
    {
        $this->components->task('Implement notification data table', function () {
            $isLaravel11OrHigher = version_compare(App::version(), '11.0', '>=');

            if ($isLaravel11OrHigher) {
                Artisan::call('make:notifications-table');
            } else {
                Artisan::call('notifications:table');
            }
        });
    }
}
