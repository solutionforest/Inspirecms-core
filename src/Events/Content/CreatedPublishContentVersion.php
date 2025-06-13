<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\ContentPublishVersion;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion;

class CreatedPublishContentVersion
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
     * @var ContentPublishVersion|Model
     */
    public $publishVersion;

    /**
     * @var ?ContentStatusOption
     */
    public $status;

    /**
     * Create a new event instance.
     *
     * @param  Content  $content
     * @param  ContentVersion  $version
     * @param  ContentPublishVersion  $publishVersion
     * @param  ?ContentStatusOption  $status
     * @return void
     */
    public function __construct($content, $version, $publishVersion, $status)
    {
        $this->content = $content;
        $this->version = $version;
        $this->publishVersion = $publishVersion;
        $this->status = $status;
    }
}
