<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion;

class CreatedContentVersion
{
    use SerializesModels;

    /**
     * @var Content|Model
     */
    public $content;

    /**
     * @var ContentVersion|Model
     */
    public $version;

    /**
     * @var ?ContentStatusOption
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
     * @param  Content  $content
     * @param  ContentVersion  $version
     * @param  ?ContentStatusOption  $status
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
