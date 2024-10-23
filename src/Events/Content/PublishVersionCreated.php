<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Queue\SerializesModels;

class PublishVersionCreated
{
    use SerializesModels;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\Content
     */
    public $content;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\ContentVersion
     */
    public $version;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\ContentPublishVersion
     */
    public $publishVersion;

    /**
     * @var ?\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption
     */
    public $status;

    /**
     * Create a new event instance.
     *
     * @param  \SolutionForest\InspireCms\Models\Contracts\Content  $content
     * @param  \SolutionForest\InspireCms\Models\Contracts\ContentVersion  $version
     * @param  \SolutionForest\InspireCms\Models\Contracts\ContentPublishVersion  $publishVersion
     * @param  ?\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption  $status
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
