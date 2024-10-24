<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Models\Navigation;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseTranslatableModelDto;

/**
 * @extends BaseTranslatableModelDto<Navigation>
 */
class NavigationDto extends BaseTranslatableModelDto
{
    /**
     * @var array<string,string>
     */
    public $url;

    /**
     * @var array<string,string>
     */
    public $title;

    /**
     * @var string
     */
    public $target;

    /**
     * @var string
     */
    public $category;

    /**
     * @var string
     */
    public $type;

    /**
     * @var Collection<NavigationDto>
     */
    public $children;

    public static function fromTranslatableModel($model, $locale)
    {
        $model->loadMissing(['content', 'children']);

        $dto = parent::fromTranslatableModel($model, $locale);

        $dto->url = collect(inspirecms()->getAllAvailableLanguages())
            ->mapWithKeys(fn ($language) => [
                $language->code => $model->getUrl($language),
            ])
            ->all();

        $dto->children = collect($model->children)
            ->map(fn ($child) => self::fromTranslatableModel($child, $locale))
            ->values();

        return $dto;
    }

    public function getTitle(?string $locale = null, bool $usingFallback = true): string
    {
        return $this->getTranslations($this->title, $locale, $usingFallback);
    }

    public function getUrl(?string $locale = null, bool $usingFallback = true): ?string
    {
        return $this->getTranslations($this->url, $locale, $usingFallback);
    }

    public function hasChildren(): bool
    {
        return $this->children->isNotEmpty() && $this->type == 'group';
    }
}
