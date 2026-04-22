<?php

namespace SolutionForest\InspireCms\Helpers;

use Filament\Pages\Page;
use SolutionForest\InspireCms\InspireCmsConfig;

/**
 * @phpstan-type FiPageClass class-string<\Filament\Pages\Page>|\Filament\Pages\Page
 */
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

    /**
     * @param  string  $routeName
     * @param  array  $parameters
     * @return string|null
     */
    public static function attemptToGetRouteFromPanel($routeName, $parameters = [])
    {
        try {

            $panel = filament()->getPanel(InspireCmsConfig::getPanelId());

            return $panel?->route($routeName, $parameters);

        } catch (\Throwable $th) {
            //
        }

        return null;
    }

    /**
     * @param  string  $routeName
     * @param  array  $parameters
     * @return string|null
     */
    public static function attemptToGetRoute($routeName, $parameters = [])
    {
        try {

            return route($routeName, $parameters);

        } catch (\Throwable $th) {
            //
        }

        return null;
    }

    /**
     * @param  FiPageClass  $target
     * @param  array  $parameters
     * @return string|null
     */
    public static function attemptToGetUrlFromPanel($target, $parameters = [])
    {
        try {

            $panelId = InspireCmsConfig::getPanelId();

            if (is_a($target, Page::class, true)) {
                return $target::getUrl(parameters: $parameters, panel: $panelId);
            }

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
