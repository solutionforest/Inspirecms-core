<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Models\Contracts\Content as ContentContract;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class Content extends BaseModel implements ContentContract
{
    use Concerns\BelongToCmsNestableTree;
    use Concerns\HasPropertyData;
    use Concerns\HasTemplates;
    use Concerns\NestableTrait;
    use Concerns\Publishable;

    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

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

    public function isPublished(?\Closure $callback = null): bool
    {
        /** @var ?\Carbon\Carbon */
        $publishedAt = $this->published_at;
        $status = $this->status;

        // If there's no publish date, it's not published
        if (is_null($publishedAt)) {
            return false;
        }

        // Check if the publish date is in the past
        if ($publishedAt->isPast()) {

            $unpublishOption = inspirecms_content_statuses()->getOption('unpublish');
            if (is_null($unpublishOption)) {
                throw new \Exception('At least one "unpublish" option is required in the manifest.');
            }

            switch ($status) {

                case $unpublishOption->getValue():
                    return false;

                default:
                    if ($callback) {
                        return $callback($this, inspirecms_content_statuses()->getOption($status));
                    }

                    return true;
            }
        }

        // If the publish date is in the future, it's not published
        return false;
    }

    protected function getPropertyDateToSave(): array
    {
        $publishedAt = $this->published_at;

        $status = $this->status;

        $defaultStatusValue = inspirecms_content_statuses()->getDefaultValue();

        if ($status === $defaultStatusValue || is_null($defaultStatusValue)) {
            return [];
        }

        // fill publish time to property data to determine is "published" version
        return [
            'published_at' => $publishedAt,
        ];
    }

    /**
     * Determine if this content is already published.
     */
    public function scopeIsPublished(Builder $query, bool $condition = true): void
    {
        $unpublishOption = inspirecms_content_statuses()->getOption('unpublish');
        if (is_null($unpublishOption)) {
            throw new \Exception('At least one "unpublish" option is required in the manifest.');
        }

        if ($condition) {

            $query
                ->where('published_at', '<', now())
                ->whereNot('status', $unpublishOption->getValue());

        } else {

            $query
                ->orWhereNull('published_at')
                ->orWhereNot('published_at', '<', now())
                ->orWhere('status', $unpublishOption->getValue());
        }

    }

    public function scopeIsRootLevel(Builder $query, bool $condition = true): void
    {
        $query->whereHas('documentType', fn ($query) => $query->where('can_use_at_root', $condition));
    }

    //endregion Scope(s)

    //region Attribute(s)
    public function displayStatus(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => inspirecms_content_statuses()->getOption($this->status),
        );
    }
    //endregion Attribute(s)

    protected function getParentId()
    {
        return $this->{$this->getNestableParentIdColumn()} ?? $this->fallbackParentId();
    }

    protected function getNestableParentIdColumn()
    {
        return 'parent_id';
    }
}
