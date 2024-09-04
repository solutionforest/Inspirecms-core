<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use SolutionForest\InspireCms\DataTypes\Manifest\UserRole;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:import-default-data')]
class ImportDefaultData extends Command
{
    public function handle(): int
    {
        $this->importLanguageData();
        $this->importLaravelPermissionData();

        return static::SUCCESS;
    }

    protected function importLanguageData(): void
    {
        $this->info('Importing language data ...');

        $model = InspireCmsConfig::getLanguageModelClass();

        // Get the table name from the model
        $tableName = (new $model)->getTable();

        if (! $this->isTableExists($tableName)) {
            return;
        }

        $model::findOrCreateDefaultLanguage();
    }

    protected function importLaravelPermissionData(): void
    {
        $this->info('Importing user role and permission data ...');

        $tableNames = config('permission.table_names');

        foreach ($tableNames as $key => $tableName) {
            if (! $this->isTableExists($tableName)) {
                return;
            }
        }

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionClass = InspireCmsConfig::getPermissionModelClass();
        $rolClass = InspireCmsConfig::getRoleModelClass();

        $permissions = PermissionManifest::permissions()->map(fn (string $permissionName) => 
            $permissionClass::findOrCreate($permissionName, InspireCmsConfig::getGuardName())
        );
        PermissionManifest::roles()->each(function (UserRole $userRole) use ($rolClass, $permissions) {

            // create roles and assign created permissions
            $role = $rolClass::findOrCreate($userRole->getName(), $userRole->getGuardName());

            // assign all permissions for "admin" role.
            if ($userRole->getName() == 'admin') {
                $role->syncPermissions($permissions);
            }
        });
    }

    /**
     * Check if the table exists, if not, display error message.
     */
    protected function isTableExists(string $tableName): bool
    {
        $exist = Schema::hasTable($tableName);
        if (! $exist) {
            $this->error("Table $tableName does not exist, please run migration first.");
        }

        return $exist;
    }
}
