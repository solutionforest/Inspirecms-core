<?php

namespace SolutionForest\InspireCms\Helpers;

class SeoHelper
{
    public static function getTranslatableAttributes(): array
    {
        return [
            'meta_title',
            'meta_description',
            'og_title',
            'og_description',
        ];
    }
}
