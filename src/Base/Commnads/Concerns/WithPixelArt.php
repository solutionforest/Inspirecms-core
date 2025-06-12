<?php

namespace SolutionForest\InspireCms\Base\Commnads\Concerns;

trait WithPixelArt
{
    private function displayPixelArtBanner($title)
    {
        $this->line('');
        $displayTitle = "   ✨  {$title}  ✨   ";
        $length = mb_strlen($displayTitle);
        $border = str_repeat('═', $length + 2);
        $this->info('╔' . $border . '╗');
        $this->info('║' . str_repeat(' ', $length + 2) . '║');
        $this->info('║' . $displayTitle . '║');
        $this->info('║' . str_repeat(' ', $length + 2) . '║');
        $this->info('╚' . $border . '╝');
        $this->line('');
    }
}
