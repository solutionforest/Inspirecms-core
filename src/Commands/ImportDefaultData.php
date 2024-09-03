<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use SolutionForest\InspireCms\DataTypes\UserRole;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
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

        inspirecms_permissions()->roles()->each(function (UserRole $role) {
            $roleModel = app(config('permission.models.role', \Spatie\Permission\Models\Role::class));
            $role = $roleModel->findOrCreate($role->name, $role->guardName);
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
