<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;
use SolutionForest\InspireCms\Database\Seeders\SampleSeeder;
use SolutionForest\InspireCms\Helpers\ModelHelper;
use SolutionForest\InspireCms\Helpers\PermissionHelper;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Throwable;

#[AsCommand(name: 'inspirecms:import-default-data')]
class ImportDefaultDataCommand extends Command
{
    protected function configure(): void
    {
        // canbe null, true, or false
        $this->addOption('skip-samples', 's', InputArgument::OPTIONAL, 'Skip importing sample data');
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

        $stepCanSkip = collect([
            'importSampleData' => $this->option('skip-samples'),
        ])->map(function ($condition, $step) use ($steps) {

            if (is_bool($condition)) {
                return $condition;
            }

            $description = lcfirst($steps[$step] ?? $step);

            return [
                $step => $this->confirm(
                    "Do you want to skip {$description}?",
                    true
                ),
            ];
        })->all();

        $skipAfter = false;
        $processErrors = [];

        foreach ($steps as $method => $description) {

            if (! method_exists($this, $method)) {
                throw new RuntimeException("Method {$method} does not exist in " . static::class);
            }

            if (array_key_exists($method, $stepCanSkip) && $stepCanSkip[$method]) {
                $skipAfter = true;
            }

            if ($skipAfter) {
                $this->components->twoColumnDetail($description, 'Skipped');
                $this->line('');

                continue;
            }

            $this->components->task($description, function () use ($method, &$skipAfter, &$processErrors) {
                try {
                    $this->$method();
                } catch (Throwable $th) {
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

        $tableName = null;

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
    }

    protected function importSampleData(): void
    {
        $this->callSilent('vendor:publish', [
            '--tag' => 'inspirecms-sample-assets',
            '--force' => true,
        ]);
        $this->callSilent('db:seed', [
            '--class' => SampleSeeder::class,
        ]);
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
        $this->callSilent('filament:assets');
    }

    protected function createSymlink(): void
    {
        $this->callSilent('storage:link');
    }

    protected function cmsRouteDefinition(): string
    {
        return '\SolutionForest\InspireCms\Facades\InspireCms::routes();';
    }
}
