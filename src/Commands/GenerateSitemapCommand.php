<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use SolutionForest\InspireCms\Factories\SitemapGeneratorFactory;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:generate-sitemap')]
class GenerateSitemapCommand extends Command
{
    public function handle()
    {
        $this->info('Generating Sitemap...');

        SitemapGeneratorFactory::create()->generateSitemapFile();

        $this->info('Sitemap generated successfully!');
    }
}
