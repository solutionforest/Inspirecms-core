<?php

namespace SolutionForest\InspireCms\Helpers;

class UrlHelper
{
    /**
     * Shortens a given URL path using the specified encoding method.
     *
     * @param string $path The URL path to be shortened.
     * @param string $encoding The encoding method to use for shortening. Default is 'base62'.
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
