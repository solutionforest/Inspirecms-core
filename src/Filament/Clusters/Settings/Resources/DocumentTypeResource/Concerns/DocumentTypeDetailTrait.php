<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Concerns;

use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Widgets\WidgetConfiguration;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Widgets\AlertOverview;

trait DocumentTypeDetailTrait
{
    public function mountDocumentTypeDetailTrait()
    {
        if ($this instanceof EditRecord || $this instanceof ViewRecord) {
            $record = $this->getRecord();
            if (is_null($record->templates_count)) {
                $record->loadCount('templates');
            }
            if (is_null($record->field_groups_count)) {
                $record->loadCount('fieldGroups');
            }
            $this->record = $record;
        }
    }

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
}
