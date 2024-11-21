<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:install-require-packages', description: 'Install required packages for InspireCMS')]
class InstallRequirePacakges extends Command
{
    public function handle(): int
    {
        $this->installFieldGroupPackage();
        $this->installSpatieLaravelPermissionPackage();
        $this->installSupportPackage();
        $this->installSpatieLaravelMediaLibraryPackage();
        $this->publishNotificationDataTable();

        return static::SUCCESS;
    }

    protected function installFieldGroupPackage()
    {
        $this->components->info('Installing SolutionForest\\FilamentFieldGroup package...');

        // Publish field group package - migration
        Artisan::call('vendor:publish', [
            '--provider' => 'SolutionForest\\FilamentFieldGroup\\FilamentFieldGroupServiceProvider',
            '--tag' => 'filament-field-group-migrations',
        ]);

        $this->components->info('SolutionForest\\FilamentFieldGroup package installed successfully.');
    }

    protected function installSpatieLaravelPermissionPackage()
    {
        $this->components->info('Installing Spatie\\LaravelPermission package...');

        Artisan::call('vendor:publish', [
            '--provider' => 'Spatie\\Permission\\PermissionServiceProvider',
        ]);

        $this->components->info('Spatie\\LaravelPermission package installed successfully.');
    }

    protected function installSupportPackage()
    {
        $this->components->info('Installing SolutionForest\\InspireCms\\Support package...');

        Artisan::call('vendor:publish', [
            '--provider' => 'SolutionForest\\InspireCms\\Support\\InspireCmsSupportServiceProvider',
            '--tag' => 'inspirecms-support-migrations',
        ]);

        $this->components->info('Spatie\\MediaLibrary package installed successfully.');
    }

    protected function installSpatieLaravelMediaLibraryPackage()
    {
        $this->components->info('Installing Spatie\\MediaLibrary package...');

        Artisan::call('vendor:publish', [
            '--provider' => 'Spatie\\MediaLibrary\\MediaLibraryServiceProvider',
            '--tag' => 'medialibrary-migrations',
        ]);

        $this->components->info('Spatie\\MediaLibrary package installed successfully.');
    }

    protected function publishNotificationDataTable()
    {
        $this->components->info('Publishing notification data table...');

        $isLaravel11OrHigher = version_compare(App::version(), '11.0', '>=');

        if ($isLaravel11OrHigher) {
            Artisan::call('make:notifications-table');
        } else {
            Artisan::call('notifications:table');
        }

        $this->components->info('Notification data table published successfully.');
    }
}
