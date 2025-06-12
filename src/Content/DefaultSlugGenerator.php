<?php

namespace SolutionForest\InspireCms\Content;

use Illuminate\Support\Str;

class DefaultSlugGenerator implements SlugGeneratorInterface
{
    public function generate($text) 
    {
        return Str::slug($text);
    }
}
