<?php

namespace SolutionForest\InspireCms\Filament\Navigation;

use Filament\Support\Concerns\HasExtraAttributes;

class MenuItem extends \Filament\Navigation\MenuItem
{
    use HasExtraAttributes;

    protected $tag = 'a';

    public function button()
    {
        $this->tag = 'button';

        return $this;
    }

    public function getTag()
    {
        return $this->tag;
    }
}
