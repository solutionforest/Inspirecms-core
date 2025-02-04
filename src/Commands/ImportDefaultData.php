<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Database\Seeders\SampleSeeder;
use SolutionForest\InspireCms\Helpers\ModelHelper;
use SolutionForest\InspireCms\Helpers\PermissionHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:import-default-data', description: 'Import default data for InspireCMS')]
class ImportDefaultData extends Command
{
    public function handle(): int
    {
        $this->publishAssets();
        $this->createSymlink();

        $this->importLanguageData();
        $this->importLaravelPermissionData();

        $this->importSampleData();

        $this->publishRouteDefinition();

        return static::SUCCESS;
    }

    protected function importLanguageData(): void
    {
        $this->components->task('Import language data', function () {

            /** @var class-string<\Illuminate\Database\Eloquent\Model> */
            $model = InspireCmsConfig::getLanguageModelClass();

            if (! $this->isTableExists($model)) {
                return;
            }

            $locale = config('app.locale', 'en');

            // Create if not exists
            $model::query()->firstOrCreate(
                ['code' => $locale],
                ['is_default' => true]
            );
        });
    }

    protected function importLaravelPermissionData(): void
    {
        $this->components->task('Import user role and permission data', function () {

            $tableNames = config('permission.table_names');

            foreach ($tableNames as $key => $tableName) {
                if (! $this->isTableExists($tableName)) {
                    return;
                }
            }

            PermissionHelper::setupSuperAdminRole();

            // Add example roles
            $roleClass = InspireCmsConfig::getRoleModelClass();
            $guardName = InspireCmsConfig::getGuardName();
            $allPermissions = PermissionHelper::setupPermissions()->filter(fn (\Spatie\Permission\Contracts\Permission $permission) => $permission->guard_name === $guardName);

            $modelPermissionFilter = fn (string $permissionName, string $action, array $models) => Str::after($permissionName, '.') == $action && in_array(Str::before($permissionName, '.'), $models);
            $clusterPermissionFilter = fn (string $permissionName, array $clusters) => Str::startsWith($permissionName, 'access_section_cluster') && in_array(Str::afterLast($permissionName, '_'), $clusters);

            /** @var \Spatie\Permission\Models\Role | \Spatie\Permission\Contracts\Role */
            $reviewer = $roleClass::findOrCreate('Reviewer', $guardName);
            $reviewer->givePermissionTo(
                $allPermissions
                    ->filter(
                        fn (\Spatie\Permission\Contracts\Permission $permission) => 
                        (
                            Str::startsWith($permission->name, 'view') && 
                            ! (
                                Str::endsWith($permission->name, 'user') || 
                                Str::endsWith($permission->name, 'role')
                            )
                        ) ||
                        str_starts_with($permission->name, 'widgets') ||
                        $clusterPermissionFilter($permission->name, ['content', 'media', 'settings'])
                    )
            );
            /** @var \Spatie\Permission\Models\Role | \Spatie\Permission\Contracts\Role */
            $writer = $roleClass::findOrCreate('Writer', $guardName);
            $writer->givePermissionTo(
                $allPermissions
                    ->filter(
                        fn (\Spatie\Permission\Contracts\Permission $permission) => 
                        str_starts_with($permission->name, 'widgets') ||
                        $modelPermissionFilter($permission->name, 'view', ['content']) ||
                        $modelPermissionFilter($permission->name, 'view_any', ['content']) ||
                        $modelPermissionFilter($permission->name, 'update', ['content']) ||
                        $modelPermissionFilter($permission->name, 'create', ['content']) ||
                        $clusterPermissionFilter($permission->name, ['content'])
                    )
            );
            /** @var \Spatie\Permission\Models\Role | \Spatie\Permission\Contracts\Role */
            $editor = $roleClass::findOrCreate('Editor', $guardName);
            $editor->givePermissionTo(
                $allPermissions
                    ->filter(
                        fn (\Spatie\Permission\Contracts\Permission $permission) => 
                        str_starts_with($permission->name, 'widgets') ||
                        $modelPermissionFilter($permission->name, 'view', ['content', 'mediaasset']) ||
                        $modelPermissionFilter($permission->name, 'view_any', ['content', 'mediaasset']) ||
                        $modelPermissionFilter($permission->name, 'create', ['content', 'mediaasset']) ||
                        $modelPermissionFilter($permission->name, 'update', ['content', 'mediaasset']) ||
                        $clusterPermissionFilter($permission->name, ['content', 'media'])
                    )
            );
        });
    }

    protected function importSampleData(): void
    {
        $this->components->info('Import sample data');

        $this->call('vendor:publish', [
            '--tag' => 'inspirecms-sample-assets',
            '--force' => true,
        ]);

        $this->call('db:seed', [
            '--class' => SampleSeeder::class,
        ]);

        $this->components->info('Sample data imported successfully.');
    }

    protected function publishRouteDefinition(): void
    {
        // Copy routes to user's routes/web.php
        $this->components->task('Publish route definition', function () {
            $routeFile = base_path('routes/web.php');

            // Replace content
            file_put_contents($routeFile, $this->cmsRouteDefinition());

        });
    }

    protected function publishAssets(): void
    {
        $this->call('filament:assets');
    }

    protected function createSymlink(): void
    {
        $this->call('storage:link');
    }

    protected function isTableExists(string $tableName): bool
    {
        if (! ModelHelper::isTableExists($tableName)) {
            $this->error("Table $tableName does not exist, please run migration first.");

            return false;
        }

        return true;
    }

    protected function cmsRouteDefinition(): string
    {
        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;

// InspireCMS routes
\SolutionForest\InspireCms\Facades\InspireCms::routes();

PHP;
    }
}
