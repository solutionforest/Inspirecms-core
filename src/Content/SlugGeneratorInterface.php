<?php

namespace SolutionForest\InspireCms\Content;

interface SlugGeneratorInterface
{
    /**
     * Generates a slug from the provided text.
     *
     * @param string $text The text to generate a slug from.
     * @param string $language The language of the text, default is auto-detect.
     * @param string $separator The separator to use between words, default is '-'.
     * @return string The generated slug.
     */
    public function generate($text, $language = DefaultSlugGenerator::LANG_AUTO, $separator = '-');
}
