<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Dtos\NavigationDto;
use SolutionForest\InspireCms\Helpers\KeyHelper;
use SolutionForest\InspireCms\Models\Contracts\Navigation as NavigationContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use SolutionForest\InspireCms\Support\Models\Concerns\NestableTrait;

class Navigation extends BaseModel implements NavigationContract
{
    use Concerns\HasTranslations;
    use HasUuids;
    use NestableTrait;

    protected $guarded = ['id'];

    protected $table = 'navigation';

    public ?array $translatable = [
        'title',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getContentModelClass(), 'content_id');
    }

    public function getUrl(): ?string
    {
        if (trim($this->type) == 'content') {
            return $this->content?->getUrl();
        }

        return $this->url;
    }

    //region Scopes
    public function scopeNavType($query, string $type)
    {
        return $query->where('navigation_type', $type);
    }
    //endregion Scopes

    //region Dto
    public function toDto(...$args)
    {
        return static::getDtoClass()::fromTranslatableModel($this, $args[0] ?? null);
    }

    public static function getDtoClass(): string
    {
        return NavigationDto::class;
    }
    //endregion Dto

    public static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            if (blank($model->{$model->getNestableParentIdColumn()})) {
                $model->{$model->getNestableParentIdColumn()} = $model->getNestableRootValue();
            }
            switch ($model->type) {
                case 'content':
                    $model->url = null;

                    break;
                case 'link':
                    $model->content_id = null;

                    break;
            }
            if (is_null($model->content_id)) {
                $model->content_id = KeyHelper::generateMinUuid();
            }
            if (is_null($model->url)) {
                $model->url = '';
            }
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
