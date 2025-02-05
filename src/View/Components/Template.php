<?php

namespace SolutionForest\InspireCms\View\Components;

use Illuminate\View\Component;
use SolutionForest\InspireCms\Dtos\ContentDto;

class Template extends Component
{
    public function __construct(
        public $content,
        public string $type = 'page',
        public ?string $locale = null,
    ) {}

    public function render()
    {
        $component = inspirecms_templates()->getComponentWithTheme($this->type);

        $locale = $this->locale;
        if ($this->content instanceof ContentDto) {
            $locale = $this->content->getLocale();
        }

        return view('components.' . $component, [
            'content' => $this->content,
            'locale' => $locale ?? app()->getLocale(),
        ]);
    }
}
