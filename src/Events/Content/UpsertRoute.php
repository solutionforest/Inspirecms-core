<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use SolutionForest\InspireCms\Models\Contracts\Content;

class UpsertRoute
{
    use SerializesModels;

    /**
     * @var Model & Content
     */
    public $content;
    public array $data = [];
    /**
     * The id to be removed.
     * @var array
     */
    public array $toRemove = [];

    /**
     * @param Model & Content $content The content to be upserted.
     * @param array $data A group of data to be upserted.
     * @param array $toRemove A group of id to be removed.
     */
    public function __construct($content, $data = [], $toRemove = [])
    {
        $this->content = $content;
        $this->data = $data;
        $this->toRemove = $toRemove;
    }
}
