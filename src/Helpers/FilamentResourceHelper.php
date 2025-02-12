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
        if (is_string($pages)) {
            $pages = [$pages];
        }

        try {
            
            if ($autorizeAction) {

                $page = static::retrieveFirstAccessiblePage($resource, $pages, $parameters);

            } else {

                $page = collect($pages)->where(fn ($page) => $resource::hasPage($page))->first();

            }
             
            if ($page) {
                return $resource::getUrl($page, $parameters);
            }


        } catch (\Throwable $th) {
            //
        }

        return null;
    }

    public static function retrieveFirstAccessiblePage(string $resource, array | string $pages, array $parameters): ?string
    {
        if (is_string($pages)) {
            $pages = [$pages];
        }

        foreach ($pages as $page) {

            if (! $resource::hasPage($page)) {
                continue;
            }

            $record = $parameters['record'] ?? null;

            $action = $page === 'index' ? 'access' : $page;

            $method = (string) str($action)->studly()->prepend('can');

            if ($resource::{$method}($record)) {
                return $page;
            }
        }

        return null;
    }
}
