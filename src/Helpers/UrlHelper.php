<?php

namespace SolutionForest\InspireCms\Helpers;

use SolutionForest\InspireCms\InspireCmsConfig;

class UrlHelper
{
    /**
     * Shortens a given URL path using the specified encoding method.
     *
     * @param  string  $path  The URL path to be shortened.
     * @param  string  $encoding  The encoding method to use for shortening. Default is 'base62'.
     * @return string The shortened URL.
     */
    public static function getShortener(string $path, string $encoding = 'base62')
    {
        if ($encoding === 'base62') {
            // Generate a unique ID for the path
            $uniqueId = crc32($path);

            return static::getShortenerBase62($uniqueId);
        }

        throw new \Exception('Invalid encoding type');
    }

    public static function attemptToGetRouteFromPanel(string $routeName, array $parameters = []): ?string
    {
        try {

            $panelId = InspireCmsConfig::get('filament.panel_id');
            $panel = filament()->getPanel($panelId);

            return $panel?->route($routeName, $parameters);
        } catch (\Throwable $th) {
            //
        }

        return null;
    }

    public static function attemptToGetRoute(string $routeName, array $parameters = [], bool $authorizeAction = true): ?string
    {
        try {

            return route($routeName, $parameters);
            
        } catch (\Throwable $th) {
            //
        }

        return null;
    }

    protected static function getShortenerBase62(string $data): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($characters);
        $result = '';

        while ($data > 0) {
            $remainder = $data % $base;
            $data = floor($data / $base);
            $result = $characters[$remainder] . $result;
        }

        return $result;
    }
}
