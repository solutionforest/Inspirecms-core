<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationCategory as NavigationCategoryEnumInterface;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationType as NavigationTypeEnumInterface;
use SolutionForest\InspireCms\Base\Enums\NavigationCategory as NavigationCategoryEnum;
use SolutionForest\InspireCms\Base\Enums\NavigationType as NavigationTypeEnum;
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
        'url',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getContentModelClass(), 'content_id');
    }

    public function getUrl(?string $locale = null): ?string
    {
        switch (trim($this->type)) {
            case NavigationTypeEnum::Link->value:
                return $this->getTranslation('url', $locale);
            case NavigationTypeEnum::Group->value:
                return null;
            case NavigationTypeEnum::Content->value:
                return $this->content?->getUrl($locale);
            default:
                return null;
        }
    }

    //region Scopes
    public function scopeCategory($query, string $type)
    {
        return $query->where('category', $type);
    }
    //endregion Scopes

    //region Enums
    public function getNavigationCategoryEnum(): ?NavigationCategoryEnumInterface
    {
        return static::getNavigationCategoryEnumClass()::tryFrom($this->category);
    }

    public static function getNavigationCategoryEnumClass(): string
    {
        $class = NavigationCategoryEnum::class;

        if (! in_array(NavigationCategoryEnumInterface::class, class_implements($class))) {
            throw new \RuntimeException("{$class} must implement " . NavigationCategoryEnumInterface::class);
        }

        return $class;
    }

    public function getNavigationTypeEnum(): ?NavigationTypeEnumInterface
    {
        return static::getNavigationCategoryEnumClass()::tryFrom($this->type);
    }

    public static function getNavigationTypeEnumClass(): string
    {
        $class = NavigationTypeEnum::class;

        if (! in_array(NavigationTypeEnumInterface::class, class_implements($class))) {
            throw new \RuntimeException("{$class} must implement " . NavigationTypeEnumInterface::class);
        }

        return $class;
    }
    //endregion Enums

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
            if ($model->type instanceof NavigationTypeEnumInterface) {
                $model->type = $model->type->value;
            }
            if ($model->category instanceof NavigationCategoryEnumInterface) {
                $model->category = $model->category->value;
            }
            switch ($model->type) {
                case NavigationTypeEnum::Content->value:
                    $model->url = null;

                    break;
                case NavigationTypeEnum::Link:
                    $model->content_id = null;

                    break;
                case NavigationTypeEnum::Group:
                    $model->content_id = null;
                    $model->url = null;

                    break;
            }
            if (blank($model->category)) {
                $model->category = static::getNavigationCategoryEnumClass()::getDefaultValue()->value;
            }
            if (is_null($model->content_id)) {
                $model->content_id = KeyHelper::generateMinUuid();
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
