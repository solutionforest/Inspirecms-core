<?php

namespace SolutionForest\InspireCms\Listeners\Content;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Events\Content\CreatedContentVersion;
use SolutionForest\InspireCms\Events\Content\CreatedPublishContentVersion;
use SolutionForest\InspireCms\Events\Content\CreatingContentVersion;
use SolutionForest\InspireCms\Events\Content\CreatingPublishContentVersion;
use SolutionForest\InspireCms\Events\Content\DispatchContentVersion;
use SolutionForest\InspireCms\Events\Content\GenerateSitemap;
use SolutionForest\InspireCms\Models\Contracts\Base\HasContentVersions;

class ProcessContentVersion
{
    public function handle(DispatchContentVersion $event)
    {
        $model = $event->model;

        try {
            $this->processContentVersion($model);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $model->resetContentVersionState();
        }
    }

    protected function processContentVersion(HasContentVersions | Model $model)
    {
        $statusOption = inspirecms_content_statuses()->getOption($model->getPublishableState());
        $isPublishing = $statusOption && $statusOption->isPublishable();

        $contentVersionData = $model->getPreloadVersionData();
        $contentVersionData['from_data'] = $contentVersionData['from'] ?? [];
        $contentVersionData['to_data'] = $contentVersionData['to'] ?? [];
        unset($contentVersionData['from'], $contentVersionData['to']);
        $contentVersionData['avoid_to_clean'] = $isPublishing;

        // Unload the relations to prevent large amounts of unnecessary data from being serialized.
        event(new CreatingContentVersion($model->withoutRelations(), $contentVersionData, $statusOption, $isPublishing));

        $contentVersion = $model->contentVersions()->create($contentVersionData);

        // Unload the relations to prevent large amounts of unnecessary data from being serialized.
        event(new CreatedContentVersion($model->withoutRelations(), $contentVersion, $statusOption, $isPublishing));

        if ($isPublishing) {
            $this->processPublishVersion($model, $contentVersion, $statusOption);
        }
    }

    protected function processPublishVersion(HasContentVersions | Model $model, Model $contentVersion, ?ContentStatusOption $statusOption)
    {
        $data = $model->getPublishableData();
        $data['version_id'] = $contentVersion->getKey();

        // Unload the relations to prevent large amounts of unnecessary data from being serialized.
        event(new CreatingPublishContentVersion($model->withoutRelations(), $contentVersion->withoutRelations(), $data, $statusOption));

        $publishVersion = $model->publishVersionLogs()->create($data);

        // Unload the relations to prevent large amounts of unnecessary data from being serialized.
        event(new GenerateSitemap($model->withoutRelations(), $model->getVersioningEvent()));
        event(new CreatedPublishContentVersion($model->withoutRelations(), $contentVersion->withoutRelations(), $publishVersion->withoutRelations(), $statusOption));
    }
}
