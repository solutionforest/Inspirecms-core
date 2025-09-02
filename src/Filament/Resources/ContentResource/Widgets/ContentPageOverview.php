<?php

namespace SolutionForest\InspireCms\Filament\Resources\ContentResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Resources\ContentResource;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Resources\Helpers\ContentResourceHelper;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentPageOverview extends Widget
{
    protected string $view = 'inspirecms::filament.widgets.content-page-overview';

    protected int | string | array $columnSpan = 'full';

    public ?Model $defaultPageRecord = null;

    public function getDefaultPageRecord(): ?Model
    {
        if ($this->defaultPageRecord) {
            return $this->defaultPageRecord;
        }

        return $this->defaultPageRecord = InspireCmsConfig::getContentModelClass()::with([
            'documentType',
        ])->whereIsDefault()->first();
    }

    public function getCreateDocumentUrl(): ?string
    {
        $resource = InspireCmsConfig::getFilamentResource('document_type', DocumentTypeResource::class);

        return FilamentResourceHelper::attemptToGetUrl($resource, ['create', 'index'], [], true);
    }

    public function canCreateContent(): bool
    {
        $resource = InspireCmsConfig::getFilamentResource('page', ContentResource::class);

        return $resource::canCreate();
    }

    public function getDefaultPageUrl(): ?string
    {
        $record = $this->getDefaultPageRecord();

        if ($record) {
            $resource = InspireCmsConfig::getFilamentResource('page', ContentResource::class);

            return FilamentResourceHelper::attemptToGetUrl($resource, ['edit', 'view'], ['record' => $record], true);
        }

        return null;
    }

    public function callAction(string $action, array $arguments = []): void
    {
        $this->dispatch('mountAction', $action, arguments: $arguments);
    }

    /**
     * @param  null | Model & Content  $content
     * @return ?string
     */
    public function getContentTitle($content)
    {
        return $content?->title;
    }

    /**
     * @param  null | Model & Content  $content
     */
    public function getContentStatusIcon($content)
    {
        return $content?->display_status?->getIcon();
    }

    /**
     * @param  null | Model & Content  $content
     */
    public function getContentStatusLabel($content)
    {
        return $content?->display_status?->getLabel();
    }

    /**
     * @param  null | Model & Content  $content
     */
    public function getContentStatusColor($content)
    {
        return $content?->display_status?->getColor();
    }

    /**
     * @param  null | Model & Content  $content
     */
    public function getContentPublishTime($content)
    {
        return ContentResourceHelper::getLatestPublishTime($content)?->diffForHumans();
    }
}
