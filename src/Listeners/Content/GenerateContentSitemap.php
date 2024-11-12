<?php

namespace SolutionForest\InspireCms\Listeners\Content;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use SolutionForest\InspireCms\Factories\SitemapGeneratorFactory;
use Throwable;

class GenerateContentSitemap implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(object $event)
    {
        $generator = SitemapGeneratorFactory::create();

        try {
            $generator->generateSitemapFile();
        } catch (Throwable $th) {
            $this->fail($th);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(object $event, Throwable $exception): void
    {
        $generator = SitemapGeneratorFactory::create();

        $generator->sendFailedNotification($exception);
    }
}
