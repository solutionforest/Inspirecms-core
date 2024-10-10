<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Helpers\KeyHelper;
use SolutionForest\InspireCms\Models\Contracts\MediaAsset as MediaAssetContract;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaAsset extends BaseModel implements MediaAssetContract
{
    use Concerns\BelongToCmsNestableTree;
    use Concerns\NestableTrait;
    use Concerns\HasAuthor;
    use HasUuids;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $guarded = ['id'];

    protected $casts = [
        'is_folder' => 'boolean',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Crop, config('inspirecms.media.preview.width', 300), config('inspirecms.media.preview.height', 300))
            ->nonQueued();
    }

    public function getFirstMedia(): ?Media
    {
        return $this->media()->first();
    }

    public function getUrl(string $conversionName = ''): ?string
    {
        $media = $this->getFirstMedia();

        return $media?->getUrl($conversionName);
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->getUrl('preview');
    }

    public function getThumbnail(): string
    {
        if ($this->isImage()) {
            return $this->getThumbnailUrl();
        }
        if ($this->isFolder()) {
            return 'heroicon-o-folder';
        }
        // Check by mime type
        $mime = $this->getFirstMedia()?->mime_type;

        if (blank($mime)) {
            return 'heroicon-s-x-mark';
        }

        if (str_starts_with($mime, 'audio/')) {
            return 'heroicon-o-music-note';
        }

        if (str_starts_with($mime, 'video/')) {
            return 'heroicon-o-film';
        }

        return 'heroicon-o-document';
    }

    public function isImage(): bool
    {
        if ($this->isFolder()) {
            return false;
        }
        // Check by mime type
        $mime = $this->getFirstMedia()?->mime_type;
        if (blank($mime)) {
            return false;
        }
        return str_starts_with($mime, 'image/');
    }

    public function isFolder(): bool
    {
        return $this->is_folder;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (blank($model->{$model->getNestableParentIdColumn()})) {
                $model->{$model->getNestableParentIdColumn()} = $model->fallbackParentId();
            }
        });
        static::deleting(function (self $model) {
            $model->children()->delete();
        });
        static::forceDeleting(function (self $model) {
            $model->children()->forceDelete();
        });
    }

    //region Nestable
    protected function getParentId()
    {
        return $this->{$this->getNestableParentIdColumn()} ?? $this->fallbackParentId();
    }

    public function getNestableParentIdColumn(): string
    {
        return 'parent_id';
    }

    protected function fallbackParentId()
    {
        return $this->getNestableRootValue();
    }

    public function getNestableRootValue(): int | string
    {
        return KeyHelper::generateMinUuid();
    }
    //endregion Nestable
}
