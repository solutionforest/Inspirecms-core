<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Prunable;
use SolutionForest\InspireCms\Helpers\DiffHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion as ContentVersionContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Models\Concerns\HasAuthor;

class ContentVersion extends BaseModel implements ContentVersionContract
{
    use HasAuthor;
    use Prunable;

    protected $guarded = ['id'];

    protected $casts = [
        'from_data' => 'array',
        'to_data' => 'array',
        'created_at' => 'datetime',
        'avoid_to_clean' => 'boolean',
    ];

    public $timestamps = false;

    public function content()
    {
        return $this->belongsTo(InspireCmsConfig::getContentModelClass(), 'content_id');
    }

    public function publishLog()
    {
        return $this->hasOne(InspireCmsConfig::getContentPublishVersionModelClass(), 'version_id');
    }

    public function getDifferences()
    {
        return DiffHelper::compareArrays(
            $this->from_data ?? [],
            $this->to_data ?? []
        );
    }

    public function getVersioningCheckDiffData()
    {
        return [
            'publish_state' => $this->publish_state ?? 'draft',
            'avoid_to_clean' => $this->avoid_to_clean ?? inspirecms_content_statuses()->getOption($this->publish_state ?? 'draft')?->isPublishable() ?? null,
        ];
    }

    // region Scopes
    public function scopeWhereIsPublished($query, bool $condition = true)
    {
        if ($condition) {
            return $query->whereHas('publishLog', function ($query) {
                $query->whereIsPublished();
            });
        } else {
            return $query->whereDoesntHave('publishLog', function ($query) {
                $query->whereIsPublished();
            });
        }
    }
    // endregion Scopes

    // region Prunable
    /**
     * Get the prunable model query.
     */
    public function prunable()
    {
        $dayAfter = now()->subDays(InspireCmsConfig::get('models.prunable.content_version.interval', 5));

        return static::query()
            ->where('avoid_to_clean', false)
            ->where('created_at', '<', $dayAfter);
    }
    // endregion Prunable
}
