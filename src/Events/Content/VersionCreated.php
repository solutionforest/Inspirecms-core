<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class VersionCreated
{
    use SerializesModels;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\Content|Model
     */
    public $content;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\ContentVersion|Model
     */
    public $version;

    /**
     * @var ?\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption
     */
    public $status;

    /**
     * Indicates whether the content version is being published.
     *
     * @var bool
     */
    public $isPublishing;

    /**
     * Create a new event instance.
     *
     * @param  \SolutionForest\InspireCms\Models\Contracts\Content  $content
     * @param  \SolutionForest\InspireCms\Models\Contracts\ContentVersion  $version
     * @param  ?\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption  $status
     * @param  bool  $isPublishing
     * @return void
     */
    public function __construct($content, $version, $status, $isPublishing)
    {
        $this->content = $content;
        $this->version = $version;
        $this->status = $status;
        $this->isPublishing = $isPublishing;
    }
}
