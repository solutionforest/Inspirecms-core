<?php

namespace SolutionForest\InspireCms\Commands;

use Composer\InstalledVersions;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'inspirecms:update',
    description: 'Update the InspireCMS plugin to the latest version'
)]
class UpdatePluginCommand extends Command
{
    private const PLUGIN_NAME = 'solution-forest/inspirecms-core';

    public function handle()
    {
        // Call install command
        $this->call('inspirecms:install');

        // Get current version of the plugin
        $currentVersion = $this->getVersion();

        $this->updatePlugin($currentVersion);
    }

    private function getVersion()
    {
        return InstalledVersions::getPrettyVersion(static::PLUGIN_NAME);
    }

    /**
     * @param  ?string  $currentVersion
     */
    private function updatePlugin($currentVersion)
    {
        if (empty($currentVersion)) {
            $this->error('Plugin not found');

            return;
        }

    }
}
