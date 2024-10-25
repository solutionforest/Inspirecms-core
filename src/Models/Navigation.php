<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kalnoy\Nestedset\NodeTrait;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationCategory as NavigationCategoryEnumInterface;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationType as NavigationTypeEnumInterface;
use SolutionForest\InspireCms\Base\Enums\NavigationCategory as NavigationCategoryEnum;
use SolutionForest\InspireCms\Base\Enums\NavigationType as NavigationTypeEnum;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Helpers\KeyHelper;
use SolutionForest\InspireCms\Models\Contracts\Navigation as NavigationContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class Navigation extends BaseModel implements NavigationContract
{
    use Concerns\HasTranslations;
    use HasUuids;
    use NodeTrait;

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

    public function getUrl(null | string | LanguageDto $locale = null): ?string
    {
        switch (trim($this->type)) {
            case NavigationTypeEnum::Link->value:
                $locale = $locale instanceof LanguageDto ? $locale->code : $locale;

                return $this->getTranslation('url', $locale ?? $this->getFallbackLocale());
            case NavigationTypeEnum::Group->value:
                return null;
            case NavigationTypeEnum::Content->value:
                $locale = $locale instanceof LanguageDto ? $locale->locale : $locale;

                return $this->content?->getUrl($locale);
            default:
                return null;
        }
    }

    //region Scopes
    public function scopeCategory($query, string $type)
    {
        $query->where('category', $type);
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
        return static::getNavigationTypeEnumClass()::tryFrom($this->type);
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

    //region Node
    protected function getScopeAttributes()
    {
        return [
            'category',
        ];
    }
    //endregion Node

    public static function defaultContentId(): string|int|null
    {
        return KeyHelper::generateMinUuid();
    }

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
                case NavigationTypeEnum::Link->value:
                    $model->content_id = null;

                    break;
                case NavigationTypeEnum::Group->value:
                    $model->content_id = null;
                    $model->url = null;

                    break;
            }
            if (blank($model->category)) {
                $model->category = static::getNavigationCategoryEnumClass()::getDefaultValue()->value;
            }
            
            // If the category is changed, make the model root
            if ($model->isDirty('category')) {
                $model->makeRoot();
            }
            if (is_null($model->content_id)) {
                $model->content_id = static::defaultContentId();
            }

            InspireCms::forgetCachedNavigation();
        });

        static::deleting(function (self $model) {
            InspireCms::forgetCachedNavigation();
        });
    }
}
