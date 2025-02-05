<?php

namespace SolutionForest\InspireCms\View\Components\Filament\Resources;

use Illuminate\View\Component;

class RelationManagers extends Component
{
    public function __construct(
        public $managers,
        public $ownerRecord,
        public $pageClass,
        public $activeManager,
        public $activeLocale = null,
        public $content = null,
        public $contentTabLabel = null,
        public $contentTabIcon = null,
        public $contentTabPosition = null,
    ) {}

    public function render()
    {
        return view('inspirecms::components.resources.relation-managers', [
            'managers' => $this->managers,
            'ownerRecord' => $this->ownerRecord,
            'pageClass' => $this->pageClass,
            'activeManager' => $this->activeManager,
            'activeLocale' => $this->activeLocale,
            'content' => $this->content,
            'contentTabLabel' => $this->contentTabLabel,
            'contentTabIcon' => $this->contentTabIcon,
            'contentTabPosition' => $this->contentTabPosition,
        ]);
    }
}
