<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Concerns;

use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Facades\FilamentIcon;
use Filament\Widgets\WidgetConfiguration;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Widgets\AlertOverview;

trait DocumentTypeDetailTrait
{
    protected function getHeaderWidgets(): array
    {
        $widgets = [];
        foreach (static::getResource()::getWidgets() as $widget) {
            $widgetFqcn = $widget instanceof WidgetConfiguration ? $widget->widget : $widget;
            $widgetProperties = $widget instanceof WidgetConfiguration ? $widget->getProperties() : [];
            if (is_a($widgetFqcn, AlertOverview::class, true) && ($this instanceof EditRecord || $this instanceof ViewRecord)) {
                if (! isset($widgetProperties['ownerRecord'])) {
                    $widgetProperties['ownerRecord'] = $this->getRecord();
                }
            }
            $widgets[] = $widgetFqcn::make($widgetProperties);
        }

        return $widgets;
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return __('inspirecms::resources/document-type.presentation.tab.label');
    }
}
