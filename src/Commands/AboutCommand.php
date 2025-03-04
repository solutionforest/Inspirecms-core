<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'inspirecms:about',
    description: 'Display information about the InspireCMS plugin'
)]
class AboutCommand extends Command
{
    public function handle()
    {
        $this->call('about', [
            '--only' => 'inspirecms',
        ]);
    }
}
