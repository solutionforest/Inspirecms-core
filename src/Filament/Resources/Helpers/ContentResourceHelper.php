<?php

namespace SolutionForest\InspireCms\Filament\Resources\Helpers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentResourceHelper
{
    /**
     * @param  null | Model & Content  $record
     * @return ?\Carbon\Carbon
     */
    public static function getLatestPublishTime($record)
    {
        return $record?->getLatestPublishedContentVersion()?->pivot->published_at;
    }
}
