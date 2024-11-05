<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Widgets;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Widgets\AlertOverview as BaseWidget;
use SolutionForest\InspireCms\Filament\Widgets\AlertOverview\Alert;

class AlertOverview extends BaseWidget
{
    public ?Model $ownerRecord = null;
    
    protected function getAlerts(): array
    {
        if (($this->ownerRecord?->templates_count ?? 0) === 0) {
            $alerts[] = Alert::make(fn () => __('inspirecms::resources/document-type.templates.hint'), 'primary');
        }

        if (($this->ownerRecord?->field_groups_count ?? 0) === 0) {
            $alerts[] = Alert::make(fn () => __('inspirecms::resources/document-type.field_groups.hint'), 'primary');
        }

        return $alerts ?? [];
    }

    public function setOwnerRecord(Model $ownerRecord): self
    {
        $this->ownerRecord = $ownerRecord;

        return $this;
    }
}
