<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'inspirecms:generate-sitemap',
    description: 'Generate a sitemap for the InspireCMS plugin'
)]
class GenerateSitemap extends Command
{
    public function handle()
    {
        $this->info('Generating Sitemap...');

        // Create the sitemap using the factory
        $sitemapGenerator = \SolutionForest\InspireCms\Factories\SitemapGeneratorFactory::create();
        $sitemapGenerator->generateSitemapFile();

        $this->info('Sitemap generated successfully!');
    }
}
