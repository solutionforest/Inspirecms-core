<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Helpers\ModelHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:import-default-data', description: 'Import default data for InspireCMS')]
class ImportDefaultData extends Command
{
    public function handle(): int
    {
        $this->importLanguageData();
        $this->importLaravelPermissionData();

        $this->importDocumentType();

        return static::SUCCESS;
    }

    protected function importLanguageData(): void
    {
        $this->info('Importing language data ...');

        $model = InspireCmsConfig::getLanguageModelClass();

        if (! $this->isTableExists($model)) {
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

        $permissions = PermissionManifest::permissions()->map(
            fn (string $permissionName) => $permissionClass::findOrCreate($permissionName, InspireCmsConfig::getGuardName())
        );

        // create roles and assign created permissions
        $role = $rolClass::findOrCreate(PermissionManifest::getSuperAdminRoleName(), InspireCmsConfig::getGuardName());

        // assign all permissions for "admin" role.
        $role->syncPermissions($permissions);
    }

    protected function importDocumentType(): void
    {
        $this->info('Importing document type data ...');

        $model = InspireCmsConfig::getDocumentTypeModelClass();

        if (! $this->isTableExists($model)) {
            return;
        }

        $model::firstOrCreate([
            'slug' => 'homepage',
        ], [
            'title' => 'Homepage',
        ]);
    }

    protected function isTableExists(string $tableName): bool
    {
        if (! ModelHelper::isTableExists($tableName)) {
            $this->error("Table $tableName does not exist, please run migration first.");

            return false;
        }

        return true;
    }
}
