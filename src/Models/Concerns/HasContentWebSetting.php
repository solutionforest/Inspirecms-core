<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Services\ContentServiceInterface;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;

trait HasContentWebSetting
{
    public function webSetting()
    {
        return $this->hasOne(InspireCmsConfig::getContentWebSettingModelClass(), 'content_id');
    }

    public function isAllowIndex()
    {
        if (! $this->relationLoaded('webSetting')) {
            $this->loadMissing('webSetting');
        }

        $robots = $this->webSetting?->robots ?? [];
        $noindex = $robots['noindex'] ?? false;

        return $noindex === false;
    }

    public function isAllowFollow()
    {
        if (! $this->relationLoaded('webSetting')) {
            $this->loadMissing('webSetting');
        }

        $robots = $this->webSetting?->robots ?? [];
        $nofollow = $robots['nofollow'] ?? false;

        return $nofollow === false;
    }

    public function isRedirectable()
    {
        if (! $this->relationLoaded('webSetting')) {
            $this->loadMissing('webSetting');
        }

        if (! blank($this->webSetting?->redirect_path)) {
            return true;
        }

        $redirectContentId = $this->webSetting?->redirect_content_id;

        if (! blank($redirectContentId) && $redirectContentId !== $this->getKey() && $redirectContentId !== KeyHelper::generateMinUuid()) {
            return true;
        }

        return false;
    }

    public function getRedirectUrl($locale = null)
    {
        if (! $this->isRedirectable()) {
            return null;
        }

        if ($redirecPath = $this->webSetting?->redirect_path) {
            return $redirecPath;
        }

        if (
            ($redirectContentId = $this->webSetting?->redirect_content_id) 
            && $redirectContentId !== $this->getKey() 
            && $redirectContentId !== KeyHelper::generateMinUuid() 
            && ($redirectContent = app(ContentServiceInterface::class)->findByIds(ids: $redirectContentId, isWebPage: true, isPublished: true, limit: 1)->first())
        ) {
            return $redirectContent->getUrl($locale);
        }

        return null;
    }

    public function getRedirectType()
    {
        if (! $this->relationLoaded('webSetting')) {
            $this->loadMissing('webSetting');
        }

        return $this->webSetting?->redirect_type ?? 302;
    }
}
