<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion as ContentVersionContract;
use SolutionForest\InspireCms\Observers\ContentVersionObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use SolutionForest\InspireCms\Support\Models\Concerns\HasAuthor;

#[ObservedBy(ContentVersionObserver::class)]
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
}
