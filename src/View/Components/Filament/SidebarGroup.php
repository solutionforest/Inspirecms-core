<?php

namespace SolutionForest\InspireCms\View\Components\Filament;

use Illuminate\View\Component;

class SidebarGroup extends Component
{
    public function __construct(
        public $active = false,
        public $collapsible = true,
        public $icon = null,
        public $items = [],
        public $label = null,
        public $sidebarCollapsible = true,
        public $subNavigation = false,
    ) {}

    public function render()
    {
        return view('inspirecms::components.filament.sidebar.group', $this->data());
    }
}
