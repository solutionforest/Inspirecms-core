<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:install-require-packages')]
class InstallRequirePacakges extends Command
{
    public function handle(): int
    {
        $this->installFieldGroupPackage();
        $this->installSpatieLaravelPermissionPackage();
        $this->installSpatieLaravelMediaLibraryPackage();

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

    protected function installSpatieLaravelMediaLibraryPackage()
    {
        $this->components->info('Installing Spatie\\MediaLibrary package...');

        Artisan::call('vendor:publish', [
            '--provider' => 'Spatie\\MediaLibrary\\MediaLibraryServiceProvider',
            '--tag' => 'medialibrary-migrations',
        ]);

        $this->components->info('Spatie\\MediaLibrary package installed successfully.');
    }
}
