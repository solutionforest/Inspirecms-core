<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\InspireCms\Models\Navigation;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseTranslatableModelDto;

/**
 * @extends BaseTranslatableModelDto<Navigation>
 */
class NavigationDto extends BaseTranslatableModelDto
{
    /**
     * @var string
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
    public $type;

    public static function fromTranslatableModel($model, $locale)
    {
        $dto = parent::fromTranslatableModel($model, $locale);

        $dto->url = $model->getUrl();
        $dto->type = $model->navigation_type;

        return $dto;
    }

    public function getTitle(?string $locale = null, bool $usingFallback = true): string
    {
        return $this->getTranslations($this->title, $locale, $usingFallback);
    }
}
