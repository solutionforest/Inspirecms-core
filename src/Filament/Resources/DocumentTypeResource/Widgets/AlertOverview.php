<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\Widgets;

use SolutionForest\InspireCms\Filament\Widgets\AlertOverview\Alert;
use SolutionForest\InspireCms\Filament\Widgets\AlertOverview as BaseWidget;

class AlertOverview extends BaseWidget
{
    public array $recordCounts = [];

    protected $listeners = [
        'refreshAlerts' => '$refresh',
    ];

    protected function getAlerts(): array
    {
        if (isset($this->recordCounts['template']) && $this->recordCounts['template'] === 0) {
            $alerts[] = Alert::make(fn () => __('inspirecms::resources/document-type.templates.hint'), 'warn');
        }

        if (isset($this->recordCounts['fieldGroups']) && $this->recordCounts['fieldGroups'] === 0) {
            $alerts[] = Alert::make(fn () => __('inspirecms::resources/document-type.field_groups.hint'), 'warn');
        }

        return $alerts ?? [];
    }
}
