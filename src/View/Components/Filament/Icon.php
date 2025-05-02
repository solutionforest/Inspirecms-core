<?php

namespace SolutionForest\InspireCms\View\Components\Filament;

use Filament\Support\Facades\FilamentIcon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;
use SolutionForest\InspireCms\Helpers\IconHelper;

class Icon extends Component
{
    public function __construct(
        public $alias = null,
        public $class = '',
        public $icon = null,
        $attributes = null,
    ) {
        // Map attributes
        if ($attributes) {
            $attributesArr = $attributes instanceof ComponentAttributeBag 
                ? $attributes->getAttributes()
                : (is_array($attributes) ? $attributes : []);
            if (isset($attributesArr['icon'])) {
                $this->icon = $attributesArr['icon'];
                unset($attributesArr['icon']);
            }
            if (isset($attributesArr['alias'])) {
                $this->alias = $attributesArr['alias'];
                unset($attributesArr['alias']);
            }

            // If the class attribute is set, append it to the existing class property
            if (isset($attributesArr['class'])) {
                $this->class .= ' '. $attributesArr['class'];
                unset($attributesArr['class']);
            }
            
            $this->withAttributes($attributesArr);
        }

        // If the icon is a custom icon, set the alias to the icon name and unset the icon property
        if (IconHelper::isCmsCustomIcon($this->icon)) {
            $this->alias = $this->icon;
            $this->icon = null; // unset($this->icon);
        }
    }

    public function render()
    {
        return view('filament::components.icon', $this->data());
    }
}
