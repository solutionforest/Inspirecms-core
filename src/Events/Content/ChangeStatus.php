<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Queue\SerializesModels;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ChangeStatus
{
    use SerializesModels;

    /**
     * @var Content|Model
     */
    public $content;

    /**
     * @var ?ContentStatusOption
     */
    public $oldStatus;

    /**
     * @var ?ContentStatusOption
     */
    public $status;

    /**
     * Create a new event instance.
     *
     * @param  Content  $content
     * @param  ?ContentStatusOption  $oldStatus
     * @param  ?ContentStatusOption  $status
     * @return void
     */
    public function __construct($content, $oldStatus, $status)
    {
        $this->content = $content;
        $this->oldStatus = $oldStatus;
        $this->status = $status;
    }
}
