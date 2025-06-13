<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;

class CreatingPublishContentVersion
{
    use SerializesModels;

    /**
     * @var Content|Model
     */
    public $content;

    /**
     * @var ContentVersion|Model
     */
    public $contentVersion;

    /**
     * @var array
     */
    public $publishData;

    /**
     * @var ?ContentStatusOption
     */
    public $status;

    /**
     * Create a new event instance.
     *
     * @param  Content  $content
     * @param  ContentVersion  $contentVersion
     * @param  array  $publishData
     * @param  ?ContentStatusOption  $status
     * @return void
     */
    public function __construct($content, $contentVersion, $publishData, $status)
    {
        $this->content = $content;
        $this->contentVersion = $contentVersion;
        $this->publishData = $publishData;
        $this->status = $status;
    }
}
