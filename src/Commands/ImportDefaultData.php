<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Database\Seeders\SampleSeeder;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\Helpers\ModelHelper;
use SolutionForest\InspireCms\Helpers\PermissionHelper;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:import-default-data', description: 'Import default data for InspireCMS')]
class ImportDefaultData extends Command
{
    protected function configure()
    {
        $this->addOption(
            name: 'skip-samples',
            shortcut: 's',
            description: 'Skip importing sample data',
        );
    }

    public function handle(): int
    {
        $steps = [
            'publishAssets' => 'Publishing assets',
            'createSymlink' => 'Creating symlink',
            'importKeyValueData' => 'Importing key/value data',
            'importLanguageData' => 'Importing language data',
            'importLaravelPermissionData' => 'Importing user role and permission data',
            'importSampleData' => 'Importing sample data',
            'publishRouteDefinition' => 'Publishing route definition',
        ];

        $skipAfter = false;
        $processErrors = [];

        foreach ($steps as $method => $description) {

            $displayStep = "  # <options=bold>{$description}</>" . PHP_EOL;
            $taskDescription = PHP_EOL;

            $this->components->twoColumnDetail($displayStep);

            if ($skipAfter) {
                $this->components->twoColumnDetail($taskDescription, 'Skipped');

                continue;
            }

            $this->components->task($taskDescription, function () use ($method, &$skipAfter, &$processErrors) {
                try {
                    $this->$method();
                } catch (\Throwable $th) {
                    $this->components->error($th->getMessage());
                    $processErrors[] = $th->getMessage();
                    $skipAfter = true;
                }
            });

            $this->newLine();
        }

        if (! empty($processErrors)) {
            $this->components->warn('There was an error during the import process.');

            return static::FAILURE;
        }

        return static::SUCCESS;
    }

    protected function importKeyValueData(): void
    {
        TemplateHelper::setupKeyValueForCurrentTemplate();
    }

    protected function importLanguageData()
    {
        // /** @var class-string<\Illuminate\Database\Eloquent\Model> */
        $model = InspireCmsConfig::getLanguageModelClass();

        if (! ModelHelper::isTableExists($model, $tableName)) {

            $this->components->error("Table $tableName does not exist.");

            return false;
        }

        $locale = config('app.locale', 'en');

        // Create if not exists
        $model::query()->firstOrCreate(
            ['code' => $locale],
            ['is_default' => true]
        );
    }

    protected function importLaravelPermissionData()
    {
        $tableNames = config('permission.table_names');

        foreach ($tableNames as $key => $tableName) {
            if (! ModelHelper::isTableExists($tableName)) {
                $this->components->error("Model $tableName does not exist.");

                return false;
            }
        }

        PermissionHelper::setupSuperAdminRole();

        // Add example roles
        $roleClass = InspireCmsConfig::getRoleModelClass();
        $guardName = AuthHelper::guardName();
        $allPermissions = PermissionHelper::setupPermissions()->filter(fn (\Spatie\Permission\Contracts\Permission $permission) => $permission->guard_name === $guardName);

        $modelPermissionFilter = fn (string $permissionName, string $action, array $models) => Str::after($permissionName, '.') == $action && in_array(Str::before($permissionName, '.'), $models);
        $clusterPermissionFilter = fn (string $permissionName, array $clusters) => Str::startsWith($permissionName, 'access_section_cluster') && in_array(Str::afterLast($permissionName, '_'), $clusters);

        /** @var \Spatie\Permission\Models\Role | \Spatie\Permission\Contracts\Role */
        $reviewer = $roleClass::findOrCreate('Reviewer', $guardName);
        $reviewer->givePermissionTo(
            $allPermissions
                ->filter(
                    fn (\Spatie\Permission\Contracts\Permission $permission) => (
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
                    fn (\Spatie\Permission\Contracts\Permission $permission) => str_starts_with($permission->name, 'widgets') ||
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
                    fn (\Spatie\Permission\Contracts\Permission $permission) => str_starts_with($permission->name, 'widgets') ||
                    $modelPermissionFilter($permission->name, 'view', ['content', 'mediaasset']) ||
                    $modelPermissionFilter($permission->name, 'view_any', ['content', 'mediaasset']) ||
                    $modelPermissionFilter($permission->name, 'create', ['content', 'mediaasset']) ||
                    $modelPermissionFilter($permission->name, 'update', ['content', 'mediaasset']) ||
                    $clusterPermissionFilter($permission->name, ['content', 'media'])
                )
        );
    }

    protected function importSampleData(): void
    {
        if (! $this->option('skip-samples')) {
            $this->callSilent('vendor:publish', [
                '--tag' => 'inspirecms-sample-assets',
                '--force' => true,
            ]);
            $this->call('db:seed', [
                '--class' => SampleSeeder::class,
            ]);
        }
    }

    protected function publishRouteDefinition(): void
    {
        $routeFile = base_path('routes/web.php');

        // Check file exists and contains the definition
        if (file_exists($routeFile) && ! Str::contains(file_get_contents($routeFile), 'InspireCms::routes();')) {
            // Append at the end of the file
            file_put_contents($routeFile, PHP_EOL . $this->cmsRouteDefinition(), FILE_APPEND);
        }
    }

    protected function publishAssets(): void
    {
        $this->call('filament:assets');
    }

    protected function createSymlink(): void
    {
        $this->call('storage:link');
    }

    protected function cmsRouteDefinition(): string
    {
        return '\SolutionForest\InspireCms\Facades\InspireCms::routes();';
    }
}
