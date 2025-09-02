<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\Concerns;

use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Database\Eloquent\Model;
use Pboivin\FilamentPeek\Facades\Peek;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\Widgets\AlertOverview;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;

trait DocumentTypeDetailTrait
{
    use HasPreviewModal;

    public function bootDocumentTypeDetailTrait()
    {
        Peek::ensurePluginIsLoaded();
        Peek::ensurePageSupportsPreviewModal($this);
        Peek::registerPreviewModal();
        Peek::registerBuilderEditor();
    }

    protected function getHeaderWidgets(): array
    {
        $widgets = [];
        foreach (static::getResource()::getWidgets() as $widget) {
            $widgetFqcn = $widget instanceof WidgetConfiguration ? $widget->widget : $widget;
            $widgetProperties = $widget instanceof WidgetConfiguration ? $widget->getProperties() : [];
            if (is_a($widgetFqcn, AlertOverview::class, true) && ($this instanceof EditRecord || $this instanceof ViewRecord)) {
                /**
                 * @var DocumentType&Model
                 */
                $ownerRecord = $this->getRecord();

                if ($ownerRecord->canManageTemplates()) {
                    $widgetProperties['recordCounts']['template'] = $ownerRecord->templates_count ?? null;
                    if (empty($widgetProperties['recordCounts']['template'])) {
                        $widgetProperties['recordCounts']['template'] = $ownerRecord->templates()->count();
                    }
                }

                $widgetProperties['recordCounts']['fieldGroups'] = $ownerRecord->field_groups_count ?? null;
                if (empty($widgetProperties['recordCounts']['fieldGroups'])) {
                    $widgetProperties['recordCounts']['fieldGroups'] = $ownerRecord->fieldGroups()->count();
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
        return __('inspirecms::resources/document-type.tabs.presentation');
    }
}
