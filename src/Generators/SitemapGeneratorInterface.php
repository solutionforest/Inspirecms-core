<?php

namespace SolutionForest\InspireCms\Generators;

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
     */
    public function generateSitemapFile(): void;

    /**
     * @return \Illuminate\Notifications\Notification|\Filament\Notifications\Notification
     */
    public function createFailedNotification();

    /**
     * Sends a notification when a process fails.
     *
     * @param  \Throwable  $exception  The exception that caused the failure.
     * @param  Model|Authenticatable|Collection|array  $notifiables
     */
    public function sendFailedNotification(\Throwable $exception, $notifiables = []): void;
}
