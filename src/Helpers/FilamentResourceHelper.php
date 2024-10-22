<?php

namespace SolutionForest\InspireCms\Helpers;

class FilamentResourceHelper
{
    /**
     * Attempts to generate a URL for a given resource based on the provided actions and parameters.
     *
     * @param  string  $resource  The resource for which the URL is being generated.
     * @param  array|string  $actions  An array of actions to be considered for URL generation.
     * @param  array  $parameters  An array of parameters to be included in the URL.
     * @param  bool  $autorizeAction  A flag indicating whether the action should be authorized.
     * @return ?string The generated URL if successful, or null if the URL could not be generated.
     */
    public static function attemptToGetUrl(string $resource, array | string $actions, array $parameters, bool $autorizeAction): ?string
    {
        $url = null;

        if (is_string($actions)) {
            $actions = [$actions];
        }

        try {
            foreach ($actions as $action) {
                if (filled($url)) {
                    continue;
                }

                if (! $resource::hasPage($action)) {
                    continue;
                }

                $record = $parameters['record'] ?? null;
                if ($autorizeAction) {

                    $method = (string) str($action)->studly()->prepend('can');
                    if (! $resource::{$method}($record)) {
                        continue;
                    }
                }

                $url = $resource::getUrl($action, $parameters);
            }

        } catch (\Throwable $th) {
            //
        }

        return $url;
    }
}
