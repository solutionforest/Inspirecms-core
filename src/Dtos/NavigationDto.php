<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseTranslatableDto;

/**
 * @extends BaseTranslatableDto<NavigationDto>
 */
class NavigationDto extends BaseTranslatableDto
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
     * @var bool
     */
    public $isActive;

    /**
     * @var Collection<NavigationDto>
     */
    public $children;

    public static function fromTranslatableArray(array $parameters, $locale, $fallbackLocale, $availableLocales = [])
    {
        $dto = parent::fromTranslatableArray($parameters, $locale, $fallbackLocale, $availableLocales);

        $dto->children = collect($parameters['children'] ?? [])
            ->map(fn ($child) => self::fromTranslatableArray($child, $locale, $fallbackLocale, $availableLocales))
            ->values();

        return $dto;
    }

    public function getTitle(?string $locale = null, bool $usingFallback = true): ?string
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

    public function toArray(): array
    {
        $list = parent::toArray();
        $list['children'] = $this->children->map(fn ($child) => $child->toArray())->all();
        unset($list['translatableAttributes']);
        return $list;
    }
}
