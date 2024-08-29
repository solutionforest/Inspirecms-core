<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Enums\PageStatus;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

/**
 * @method static Builder|static query()
 * @method Builder|static isRootLevel()
 */
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

    /**
     * Determine if this content is already published.
     *
     * @return bool|Builder|static
     */
    public function isPublished(bool $isIncludePrivateUse = false)
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

            switch ($status) {

                case PageStatus::Unpublish->value:
                    return false;

                case PageStatus::Private->value:
                    if ($isIncludePrivateUse) {
                        return true;
                    }

                    return false;

                default:
                    return true;
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

    /**
     * Determine if this content is already published, no matter public or private use.
     */
    public function scopeIsPublished(Builder $query, bool $condition = true, bool $isIncludePrivateUse = false): void
    {
        // - Status always "Draft" on "Save draft" button
        // - Change to "Publish" only on "Publish" button (save with "published_at" data)
        // - Change to "Unpublish" only on "Unpublish" button
        // - TODO: private button

        if ($condition) {

            $query
                ->where('published_at', '<', now())
                ->whereNot('status', PageStatus::Unpublish->value)
                ->when(
                    $isIncludePrivateUse,
                    fn ($query) => $query,
                    fn ($query) => $query->whereNot('status', PageStatus::Private->value)
                );

        } else {

            $query
                ->orWhereNull('published_at')
                ->orWhereNot('published_at', '<', now())
                ->orWhere('status', PageStatus::Unpublish->value)
                ->when(
                    $isIncludePrivateUse,
                    fn ($query) => $query,
                    fn ($query) => $query->orWhere('status', PageStatus::Private->value)
                );
        }

    }

    public function scopeIsRootLevel(Builder $query, bool $condition = true): void
    {
        $query->whereHas('documentType', fn ($query) => $query->where('can_use_at_root', $condition));
    }

    //endregion Scope(s)

    protected function getParentId()
    {
        return $this->{$this->getNestableParentIdColumn()} ?? $this->fallbackParentId();
    }

    protected function getNestableParentIdColumn()
    {
        return 'parent_id';
    }
}
