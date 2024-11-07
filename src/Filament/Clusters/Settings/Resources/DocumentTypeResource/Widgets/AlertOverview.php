<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Widgets;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Widgets\AlertOverview\Alert;
use SolutionForest\InspireCms\Filament\Widgets\AlertOverview as BaseWidget;

class AlertOverview extends BaseWidget
{
    public ?Model $ownerRecord = null;

    protected $listeners = [
        'refreshAlerts' => '$refresh',
    ];

    protected function getAlerts(): array
    {
        $templateRequired = $this->ownerRecord?->canManageTemplates() ?? false;

        if ($this->ownerRecord) {

            if ($templateRequired && is_null($this->ownerRecord->templates_count)) {
                $this->ownerRecord->loadCount('templates');
            }
            if (is_null($this->ownerRecord->field_groups_count)) {
                $this->ownerRecord->loadCount('fieldGroups');
            }
        }

        if ($templateRequired && ($this->ownerRecord?->templates_count ?? 0) === 0) {
            $alerts[] = Alert::make(fn () => __('inspirecms::resources/document-type.templates.hint'), 'warn');
        }

        if (($this->ownerRecord?->field_groups_count ?? 0) === 0) {
            $alerts[] = Alert::make(fn () => __('inspirecms::resources/document-type.field_groups.hint'), 'warn');
        }

        return $alerts ?? [];
    }

    public function setOwnerRecord(Model $ownerRecord): self
    {
        $this->ownerRecord = $ownerRecord;

        return $this;
    }
}
