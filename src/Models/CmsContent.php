<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Enums\PageStatus;
use SolutionForest\InspireCms\Enums\PageVersioningStatus;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class CmsContent extends Model
{
    use Concerns\BelongToCmsComponentTree;
    use Concerns\HasPropertyData;
    use Concerns\NestableTrait;

    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(InspireCmsConfig::getContentTableName());
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getDocumentTypeModelClass(), 'document_type_id');
    }

    /**
     * Generate a full slug base on parent.
     */
    public function generateFullSlug(): string
    {
        // todo
        return '';
    }

    public function isPublished(bool $isPrivateUse = false): bool
    {
        $publishedAt = $this->published_at;

        if (is_null($publishedAt)) {
            return false;
        }

        $status = $this->status;

        $publishStatus = [
            PageStatus::SchedulePublish->value,
            PageStatus::Publish->value,
        ];
        if ($isPrivateUse) {
            $publishStatus[] = PageStatus::Private->value;
        }

        if (! in_array($status, $publishStatus)) {
            return false;
        }

        return $publishedAt->isPast();
    }

    public function getVersioningStatus(): ?PageVersioningStatus
    {
        $latestPropertyData = $this->latestPropertyData;

        if (is_null($latestPropertyData)) {
            return null;
        }

        $latestPropertyDataPublishedAt = $latestPropertyData->published_at;

        $pagePublishedAt = $this->published_at;

        if (is_null($pagePublishedAt) ||
            is_null($latestPropertyDataPublishedAt) ||
            $latestPropertyDataPublishedAt != $pagePublishedAt) {

            return PageVersioningStatus::Draft;
        }

        return PageVersioningStatus::Published;
    }

    protected function getPropertyDateToSave()
    {
        $publishedAt = $this->published_at;

        $status = $this->status;

        if ($status === PageStatus::Pending->value) {
            return [];
        }

        ray($publishedAt, $status);

        return [
            'published_at' => $publishedAt,
        ];
    }

    public function scopeIsRoot($query, bool $condition = true)
    {
        if ($condition) {
            $query->whereNull('parent_id');
        } else {
            $query->whereNotNull('parent_id');
        }
    }

    protected function getParentId()
    {
        return $this->{$this->getNestableParentIdColumn()} ?? $this->fallbackParentId();
    }

    protected function getNestableParentIdColumn()
    {
        return 'parent_id';
    }
}
