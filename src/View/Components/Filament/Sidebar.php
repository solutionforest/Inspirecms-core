<?php

namespace SolutionForest\InspireCms\View\Components\Filament;

use Illuminate\View\Component;

class Sidebar extends Component
{
    public function __construct(
        public $navigation
    ) {}

    public function render()
    {
        return view('inspirecms::components.filament.sidebar.index', [
            'navigation' => $this->navigation,
        ]);
    }
}
