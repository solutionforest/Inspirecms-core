<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasOne;
use SolutionForest\InspireCms\Helpers\KeyHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

trait HasContentWebSetting
{
    public function webSetting(): HasOne
    {
        return $this->hasOne(InspireCmsConfig::getContentWebSettingModelClass(), 'content_id');
    }

    public function isAllowIndex(): bool
    {
        if (! $this->relationLoaded('webSetting')) {
            $this->loadMissing('webSetting');
        }

        $robots = $this->webSetting?->robots ?? [];
        $noindex = $robots['noindex'] ?? false;

        return $noindex === false;
    }

    public function isAllowFollow(): bool
    {
        if (! $this->relationLoaded('webSetting')) {
            $this->loadMissing('webSetting');
        }

        $robots = $this->webSetting?->robots ?? [];
        $nofollow = $robots['nofollow'] ?? false;

        return $nofollow === false;
    }

    public function isRedirectable(): bool
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

    public function getRedirectUrl(?string $locale = null): ?string
    {
        if (! $this->isRedirectable()) {
            return null;
        }

        if ($redirecPath = $this->webSetting?->redirect_path) {
            return $redirecPath;
        }

        if (($redirectContentId = $this->webSetting?->redirect_content_id) && $redirectContentId !== $this->getKey() && $redirectContentId !== KeyHelper::generateMinUuid()) {

            $content = $this->newQuery()->whereIsPublished()->find($redirectContentId);

            if ($content) {
                return $content->getUrl($locale);
            }
        }

        return null;
    }

    public function getRedirectType(): int
    {
        if (! $this->relationLoaded('webSetting')) {
            $this->loadMissing('webSetting');
        }

        return $this->webSetting?->redirect_type ?? 302;
    }
}
