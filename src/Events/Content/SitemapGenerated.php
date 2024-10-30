<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Queue\SerializesModels;

class SitemapGenerated
{
    use SerializesModels;

    /**
     * @var string
     */
    public $path;

    public function __construct($path)
    {
        $this->path = $path;
    }
}
