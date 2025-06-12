<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use SolutionForest\InspireCms\Base\Commnads\Concerns\WithPixelArt;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:routes', description: 'List all registered content routes')]
class RoutesCommand extends Command
{
    use WithPixelArt;

    public function handle()
    {
        $routes = $this->getRoutes();

        if (empty($routes)) {
            $this->info('No routes found for InspireCMS.');

            return;
        }

        $this->displayPixelArtBanner('InspireCMS Registered Routes');

        $this->table(
            ['URL Pattern', 'Name', 'Bindings', 'Middleware'],
            $routes,
        );

        $this->line('----------------------------------');
        $this->info('Total Routes: ' . count($routes));
        $this->line('----------------------------------');

    }

    protected function getRoutes()
    {
        $routes = [];

        foreach (app('router')->getRoutes() as $route) {
            if (str_contains($route->getName(), 'inspirecms.frontend')) {
                $routes[] = [
                    'url' => $route->uri(),
                    'name' => $route->getName(),
                    'bindings' => implode(', ', $route->parameterNames()) . (
                        $route->wheres ? ' (' . collect($route->wheres)->map(function ($value, $key) {
                            return $key . '=' . $value;
                        })->implode(', ') . ')' : ''
                    ),
                    'middleware' => implode(', ', $route->gatherMiddleware()),
                ];
            }
        }

        return $routes;
    }
}
