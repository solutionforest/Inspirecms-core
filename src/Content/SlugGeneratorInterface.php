<?php

namespace SolutionForest\InspireCms\Content;

interface SlugGeneratorInterface
{
    /**
     * Generates a slug from the provided text.
     *
     * @param  string  $text  The text to convert into a slug
     * @return string The generated slug
     */
    public function generate($text);
}
