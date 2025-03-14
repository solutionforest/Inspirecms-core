<?php

namespace SolutionForest\InspireCms\Events\Template;

class CreateTheme
{
    public function __construct(
        public string $theme,

        /**
         * The theme to clone from.
         *
         * @var string|null
         */
        public ?string $cloneFrom = null,
    ) {
        //
    }
}
