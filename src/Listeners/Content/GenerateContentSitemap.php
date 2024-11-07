<?php

namespace SolutionForest\InspireCms\Listeners\Content;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use SolutionForest\InspireCms\Factories\SitemapGeneratorFactory;

class GenerateContentSitemap implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(object $event)
    {
        $generator = SitemapGeneratorFactory::create();

        $generator->generateSitemapFile();
    }
}
