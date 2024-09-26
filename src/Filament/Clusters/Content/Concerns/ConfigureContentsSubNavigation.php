<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Concerns;

use SolutionForest\InspireCms\Support\InspireCmsConfig;

trait ConfigureContentsSubNavigation
{
    public function generateNavigationItems(array $components): array
    {
        //todo: cache this
        $rootLevelContents = InspireCmsConfig::getContentModelClass()::isRootLevel()->get();

        $items = [];

        foreach ($rootLevelContents as $rootLevelContent) {

            $parameters = $this->getSubNavigationParameters();

            $parameters['parent'] = $rootLevelContent;

            foreach ($components as $component) {
                $isResourcePage = is_subclass_of($component, ResourcePage::class);

                $shouldRegisterNavigation = $isResourcePage ?
                    $component::shouldRegisterNavigation($parameters) :
                    $component::shouldRegisterNavigation();

                if (! $shouldRegisterNavigation) {
                    continue;
                }

                $canAccess = $isResourcePage ?
                    $component::canAccess($parameters) :
                    $component::canAccess();

                if (! $canAccess) {
                    continue;
                }

                $pageItems = $isResourcePage ?
                    $component::getNavigationItems($parameters) :
                    (method_exists($component, 'getCustomNavigationItems') ? $component::getCustomNavigationItems($parameters) : $component::getNavigationItems());

                $items = [
                    ...$items,
                    ...$pageItems,
                ];
            }
        }

        return $items;
    }
}
