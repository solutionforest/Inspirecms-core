<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasOne;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

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
}
