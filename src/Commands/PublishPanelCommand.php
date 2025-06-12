<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\CmsPanelProvider;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:publish-panel', description: 'Publish CMS panel')]
class PublishPanelCommand extends Command
{
    public function handle(): int
    {
        $this->publishPanelProvider(CmsPanelProvider::class);

        $this->components->info('CMS panel installed successfully.');

        return static::SUCCESS;
    }

    private function publishPanelProvider($panelProviderFQCN)
    {
        $isLaravel11OrHigherWithBootstrapProvidersFile = version_compare(App::version(), '11.0', '>=') &&
            /** @phpstan-ignore-next-line */
            file_exists($bootstrapProvidersPath = App::getBootstrapProvidersPath());

        if ($isLaravel11OrHigherWithBootstrapProvidersFile) {

            // Publish the panel provider to the bootstrap providers file if it doesn't exist
            if (file_exists($bootstrapProvidersPath)) {
                $bootstrapProviders = file_get_contents($bootstrapProvidersPath);
                if (Str::contains($bootstrapProviders, $panelProviderFQCN)) {
                    $this->components->info('CMS panel provider already exists in bootstrap providers file.');

                    return;
                }
            }
            /** @phpstan-ignore-next-line */
            ServiceProvider::addProviderToBootstrapFile(
                $panelProviderFQCN,
                /** @phpstan-ignore-next-line */
                $bootstrapProvidersPath,
            );
        } else {
            $appConfig = file_get_contents(config_path('app.php'));

            if (! Str::contains($appConfig, "{$panelProviderFQCN}::class")) {
                file_put_contents(config_path('app.php'), str_replace(
                    'App\\Providers\\RouteServiceProvider::class,',
                    "{$panelProviderFQCN}::class," . PHP_EOL . '        App\\Providers\\RouteServiceProvider::class,',
                    $appConfig,
                ));
            }
        }
    }
}
