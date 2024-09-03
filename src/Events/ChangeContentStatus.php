<?php

namespace SolutionForest\InspireCms\Events;

use Illuminate\Queue\SerializesModels;

class ChangeContentStatus
{
    use SerializesModels;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\Content
     */
    public $content;

    /**
     * @var ?\SolutionForest\InspireCms\DataTypes\ContentStatusOption
     */
    public $status;

    /**
     * Create a new event instance.
     *
     * @param  \SolutionForest\InspireCms\Models\Contracts\Content  $content
     * @param  ?\SolutionForest\InspireCms\DataTypes\ContentStatusOption  $status
     * @return void
     */
    public function __construct($content, $status)
    {
        $this->content = $content;
        $this->status = $status;
    }
}
