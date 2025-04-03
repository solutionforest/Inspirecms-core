<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'inspirecms:update',
    description: 'Update the InspireCMS plugin to the latest version'
)]
class UpdatePluginCommand extends Command
{
    public function handle()
    {
        // publish migrations
        $this->components->task('Publishing migrations', function () {
            $this->call('vendor:publish', [
                '--provider' => 'SolutionForest\\InspireCms\\InspireCmsServiceProvider',
                '--tag' => 'inspirecms-migrations',
            ]);
        });

        // Call install command
        $this->call('inspirecms:install');

        $this->updatePlugin(inspirecms()->version());
    }

    /**
     * @param  ?string  $currentVersion
     */
    private function updatePlugin($currentVersion)
    {
        if (empty($currentVersion)) {
            $this->components->error('Plugin not found');

            return;
        }

    }
}
