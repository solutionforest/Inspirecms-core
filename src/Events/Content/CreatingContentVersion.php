<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class CreatingContentVersion
{
    use SerializesModels;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\Content|Model
     */
    public $content;

    /**
     * @var array
     */
    public $versionData;

    /**
     * @var ?\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption
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
     * @param  \SolutionForest\InspireCms\Models\Contracts\Content  $content
     * @param  array  $versionData
     * @param  ?\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption  $status
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
