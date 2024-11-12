<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion as ContentVersionContract;
use SolutionForest\InspireCms\Observers\ContentVersionObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Models\Concerns\HasAuthor;

class ContentVersion extends BaseModel implements ContentVersionContract
{
    use HasAuthor;

    protected $guarded = ['id'];

    protected $casts = [
        'from_data' => 'array',
        'to_data' => 'array',
        'created_at' => 'datetime',
        'avoid_to_clean' => 'boolean',
    ];

    public $timestamps = false;

    public function content(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getContentModelClass(), 'content_id');
    }

    public function publishLog(): HasOne
    {
        return $this->hasOne(InspireCmsConfig::getContentPublishVersionModelClass(), 'version_id');
    }

    public function getDifferences(): array
    {
        $from = $this->from_data;
        $to = $this->to_data;

        $diff = [];

        foreach ($to as $key => $value) {
            if (! array_key_exists($key, $from)) {
                $diff[$key] = [
                    'from' => null,
                    'to' => $value,
                ];
            } elseif ($from[$key] !== $value) {
                $diff[$key] = [
                    'from' => $from[$key],
                    'to' => $value,
                ];
            }
        }

        return $diff;
    }

    //region Scopes
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
    //endregion Scopes

    public static function boot()
    {
        parent::boot();

        static::observe(ContentVersionObserver::class);
    }
}
