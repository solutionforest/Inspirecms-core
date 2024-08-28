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

        return static::SUCCESS;
    }

    protected function installFieldGroupPackage()
    {
        $this->components->info('Installing field group package...');

        // Publish field group package - migration
        Artisan::call('vendor:publish', [
            '--provider' => "SolutionForest\\FilamentFieldGroup\\FilamentFieldGroupServiceProvider",
            '--tag' => 'filament-field-group-migrations',
        ]);

        $this->components->info('Field group package installed successfully.');
    }
}
