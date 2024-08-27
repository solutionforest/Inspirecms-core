<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Enums\PageStatus;
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
        $status = $this->status;

        // If there's no publish date, it's not published
        if (is_null($publishedAt)) {
            return false;
        }

        // Check if the publish date is in the past
        if ($publishedAt->isPast()) {
            // Special case: if status is "Private" and it's for private use, consider it published
            if ($status == PageStatus::Private->value && $isPrivateUse) {
                return true;
            } elseif (! ($status == PageStatus::Private->value)) {
                return true;
            } else {
                return false;
            }
        }

        // If the publish date is in the future, it's not published
        return false;
    }

    protected function getPropertyDateToSave()
    {
        $publishedAt = $this->published_at;

        $status = $this->status;

        if ($status === PageStatus::Draft->value) {
            return [];
        }

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
