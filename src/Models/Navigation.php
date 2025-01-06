<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Kalnoy\Nestedset\NodeTrait;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationType as NavigationTypeEnumInterface;
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

    /** {@inheritDoc} */
    public function content()
    {
        return $this->belongsTo(InspireCmsConfig::getContentModelClass(), 'content_id');
    }

    /** {@inheritDoc} */
    public function getUrl($locale = null)
    {
        switch (trim($this->type)) {
            case NavigationTypeEnum::Link->value:
                $locale = $locale instanceof LanguageDto ? $locale->code : $locale;

                return $this->getTranslation('url', $locale ?? $this->getFallbackLocale());
            case NavigationTypeEnum::Group->value:
                return null;
            case NavigationTypeEnum::Content->value:
                $locale = $locale instanceof LanguageDto ? $locale->code : $locale;

                return $this->content?->getUrl($locale);
            default:
                return null;
        }
    }

    // region Scopes
    public function scopeCategory($query, string $type)
    {
        return $query->where('category', $type);
    }

    public function scopeWhereIsActive($query, bool $condition = true)
    {
        return $query->where('is_active', $condition);
    }
    // endregion Scopes

    public function isVisibility()
    {
        return $this->is_active;
    }

    // region Enums
    // endregion Enums

    // region Node
    protected function getScopeAttributes()
    {
        return [
            'category',
        ];
    }
    // endregion Node

    public static function defaultContentId()
    {
        return KeyHelper::generateMinUuid();
    }

    public function setDisable(bool $save = true)
    {
        $this->is_active = false;
        if ($save) {
            $this->save();
        }
    }

    public function setEnable(bool $save = true)
    {
        $this->is_active = true;
        if ($save) {
            $this->save();
        }
    }

    // region Attribute(s)
    protected function displayType(): Attribute
    {
        return Attribute::make(
            get: function () {
                $type = $this->type;
                if (filled($type)) {
                    return static::getNavigationTypeEnumClass()::tryFrom($type);
                }

                return null;
            },
        );
    }
    // endregion Attribute(s)

    public static function boot()
    {
        parent::boot();

        static::observe(NavigationObserver::class);
    }

    public static function getNavigationTypeEnumClass()
    {
        $class = NavigationTypeEnum::class;

        if (! in_array(NavigationTypeEnumInterface::class, class_implements($class))) {
            throw new \RuntimeException("{$class} must implement " . NavigationTypeEnumInterface::class);
        }

        return $class;
    }
}
