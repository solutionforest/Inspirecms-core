<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Widgets;

use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationCategory as NavigationCategoryInterface;
use SolutionForest\InspireCms\Base\Enums\NavigationCategory;

class MainNavigation extends BaseTreeNavigation
{
    protected function getNavigationCategory(): NavigationCategoryInterface
    {
        return NavigationCategory::Main;
    }

    public function getTreeTitle(): ?string
    {
        return $this->getNavigationCategory()->getLabel();
    }
}
