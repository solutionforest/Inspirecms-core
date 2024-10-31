<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class CreatingPublishContentVersion
{
    use SerializesModels;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\Content|Model
     */
    public $content;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\ContentVersion|Model
     */
    public $contentVersion;

    /**
     * @var array
     */
    public $publishData;

    /**
     * @var ?\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption
     */
    public $status;

    /**
     * Create a new event instance.
     *
     * @param  \SolutionForest\InspireCms\Models\Contracts\Content  $content
     * @param  \SolutionForest\InspireCms\Models\Contracts\ContentVersion  $contentVersion
     * @param  array  $publishData
     * @param  ?\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption  $status
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
