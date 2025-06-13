<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Models\Contracts\Content;

class CreatingContentVersion
{
    use SerializesModels;

    /**
     * @var Content|Model
     */
    public $content;

    /**
     * @var array
     */
    public $versionData;

    /**
     * @var ?ContentStatusOption
     */
    public $status;

    /**
     * Indicates whether the content versionData is being published.
     *
     * @var bool
     */
    public $isPublishing;

    /**
     * Create a new event instance.
     *
     * @param  Content  $content
     * @param  array  $versionData
     * @param  ?ContentStatusOption  $status
     * @param  bool  $isPublishing
     * @return void
     */
    public function __construct($content, $versionData, $status, $isPublishing)
    {
        $this->content = $content;
        $this->versionData = $versionData;
        $this->status = $status;
        $this->isPublishing = $isPublishing;
    }
}
