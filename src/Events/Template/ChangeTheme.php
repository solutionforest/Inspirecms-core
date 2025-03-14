<?php

namespace SolutionForest\InspireCms\Events\Template;

class ChangeTheme
{
    public function __construct(
        public string $oldTheme,
        public string $newTheme,
    ) {
        //
    }
}
