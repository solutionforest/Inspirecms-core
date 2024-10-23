<?php

namespace SolutionForest\InspireCms\Helpers;

class FilamentResourceHelper
{
    /**
     * Attempts to generate a URL for a given resource based on the provided actions and parameters.
     *
     * @param  string  $resource  The resource for which the URL is being generated.
     * @param  array|string  $pages  The pages associated with the resource.
     * @param  array  $parameters  An array of parameters to be included in the URL.
     * @param  bool  $autorizeAction  A flag indicating whether the action should be authorized.
     * @return ?string The generated URL if successful, or null if the URL could not be generated.
     */
    public static function attemptToGetUrl(string $resource, array | string $pages, array $parameters, bool $autorizeAction): ?string
    {
        $url = null;

        if (is_string($pages)) {
            $pages = [$pages];
        }

        try {
            foreach ($pages as $page) {
                if (filled($url)) {
                    continue;
                }

                if (! $resource::hasPage($page)) {
                    continue;
                }

                $record = $parameters['record'] ?? null;

                if ($autorizeAction) {

                    $action = $page === 'index' ? 'access' : $page;

                    $method = (string) str($action)->studly()->prepend('can');

                    if (! $resource::{$method}($record)) {
                        continue;
                    }
                }

                $url = $resource::getUrl($page, $parameters);
            }

        } catch (\Throwable $th) {
            //
            dd($th);
        }

        return $url;
    }
}
