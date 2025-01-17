<?php

namespace SolutionForest\InspireCms\Sitemap;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface SitemapGeneratorInterface
{
    /**
     * Generates the sitemap file.
     *
     * This method is responsible for creating the sitemap file
     * which is used by search engines to index the website's pages.
     * 
     * @return void
     */
    public function generateSitemapFile();
    
    /**
     * Get the file path for the sitemap.
     *
     * @return string The file path of the sitemap.
     * 
     * @throws \Exception If the sitemap file path is not set in the config file.
     */
    public function getFilePath();

    /**
     * Sends a notification when a process fails.
     *
     * @param  \Throwable  $exception  The exception that caused the failure.
     * @param  Model|Authenticatable|Collection|array  $notifiables
     * 
     * @return void
     */
    public function sendFailedNotification(\Throwable $exception, $notifiables = []);
}
