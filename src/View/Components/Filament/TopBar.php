<?php

namespace SolutionForest\InspireCms\View\Components\Filament;

use Illuminate\View\Component;

class TopBar extends Component
{
    public function __construct(
        public $navigation
    ) {}

    public function render()
    {
        return view('inspirecms::components.filament.topbar.index', [
            'navigation' => $this->navigation,
        ]);
    }
}
