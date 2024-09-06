<?php

namespace SolutionForest\InspireCms\Events;

use Illuminate\Queue\SerializesModels;

class CreateTemplate
{
    use SerializesModels;

    /**
     * @var \SolutionForest\InspireCms\Models\Contracts\Template
     */
    public $template;

    /**
     * Create a new event instance.
     *
     * @param  \SolutionForest\InspireCms\Models\Contracts\Template  $template
     * @return void
     */
    public function __construct($template)
    {
        $this->template = $template;
    }
}
