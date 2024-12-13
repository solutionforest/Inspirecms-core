<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use SolutionForest\InspireCms\Helpers\PermissionHelper;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:repair-permissions', description: 'Repair permission issues for InspireCMS')]
class RepairPermissionsCommand extends Command
{
    public function handle(): int
    {
        PermissionHelper::setupPermissions();

        return static::SUCCESS;
    }
}
