<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kalnoy\Nestedset\NodeTrait;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationCategory as NavigationCategoryEnumInterface;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationType as NavigationTypeEnumInterface;
use SolutionForest\InspireCms\Base\Enums\NavigationCategory as NavigationCategoryEnum;
use SolutionForest\InspireCms\Base\Enums\NavigationType as NavigationTypeEnum;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Navigation as NavigationContract;
use SolutionForest\InspireCms\Observers\NavigationObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;

class Navigation extends BaseModel implements NavigationContract
{
    use Concerns\HasTranslations;
    use NodeTrait;

    protected $guarded = ['id'];

    protected $table = 'navigation';

    protected $casts = [
        'is_active' => 'boolean',
    ];

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

    public function scopeWhereIsActive($query, bool $condition = true)
    {
        $query->where('is_active', $condition);
    }
    //endregion Scopes

    public function isVisibility(): bool
    {
        return $this->is_active;
    }

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

    public static function defaultContentId(): string | int | null
    {
        return KeyHelper::generateMinUuid();
    }

    public function setDisable(bool $save = true): void
    {
        $this->is_active = false;
        if ($save) {
            $this->save();
        }
    }

    public function setEnable(bool $save = true): void
    {
        $this->is_active = true;
        if ($save) {
            $this->save();
        }
    }

    public static function boot()
    {
        parent::boot();

        static::observe(NavigationObserver::class);
    }
}
