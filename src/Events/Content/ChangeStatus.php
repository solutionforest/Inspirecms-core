<?php

namespace SolutionForest\InspireCms\Events\Content;

use Illuminate\Queue\SerializesModels;

class ChangeStatus
{
    use SerializesModels;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\Content|Model
     */
    public $content;

    /**
     * @var ?\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption
     */
    public $oldStatus;

    /**
     * @var ?\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption
     */
    public $status;

    /**
     * Create a new event instance.
     *
     * @param  \SolutionForest\InspireCms\Models\Contracts\Content  $content
     * @param  ?\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption  $oldStatus
     * @param  ?\SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption  $status
     * @return void
     */
    public function __construct($content, $oldStatus, $status)
    {
        $this->content = $content;
        $this->oldStatus = $oldStatus;
        $this->status = $status;
    }
}
