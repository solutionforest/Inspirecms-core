<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
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
        $this->publishRotueDefinition();

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

    protected function publishRotueDefinition(): void
    {
        // Copy routes to user's routes/web.php

        $this->info('Publishing route definition ...');

        $destination = base_path('routes/web.php');

        if (! Str::contains(file_get_contents($destination), 'InspireCms::routes()')) {
            (new Filesystem)->append($destination, $this->cmsRotueDefinition());
        }
    }

    protected function isTableExists(string $tableName): bool
    {
        if (! ModelHelper::isTableExists($tableName)) {
            $this->error("Table $tableName does not exist, please run migration first.");

            return false;
        }

        return true;
    }

    protected function cmsRotueDefinition(): string
    {
        return <<<PHP

// InspireCMS routes
\SolutionForest\InspireCms\Facades\InspireCms::routes();

PHP;
    }
}
