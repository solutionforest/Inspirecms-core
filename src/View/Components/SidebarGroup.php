<?php

namespace SolutionForest\InspireCms\View\Components;

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
        return view('inspirecms::components.sidebar.group', [
            'active' => $this->active,
            'collapsible' => $this->collapsible,
            'icon' => $this->icon,
            'items' => $this->items,
            'label' => $this->label,
            'sidebarCollapsible' => $this->sidebarCollapsible,
            'subNavigation' => $this->subNavigation,
        ]);
    }
}
