<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Dtos\Concerns\Translatable;

/**
 * @template TModle of Model
 */
abstract class BaseTranslatableModelDto extends BaseModelDto
{
    use Translatable;

    protected array $translatableAttributes = [];

    /**
     * @return self
     */
    public static function fromArray(array $parameters)
    {
        return parent::fromArray($parameters);
    }

    /**
     * @param  TModle  $model
     * @param string
     * @return self
     */
    public static function fromModel($model)
    {
        return parent::fromModel($model);
    }

    /**
     * @param  TModle  $model
     * @param string
     * @return self
     */
    public static function fromTranslatableModel($model, $locale)
    {
        $dto = static::fromModel($model)->setLocale($locale);

        if (in_array(\SolutionForest\InspireCms\Models\Concerns\HasTranslations::class, class_uses_recursive($model))) {
            $dto = $dto->setFallbackLocale($model->getFallbackLocale());
        }

        return $dto;
    }

    protected function getTranslation(string $name, ?string $locale = null, bool $usingFallback = true)
    {
        if (! in_array($name, $this->translatableAttributes)) {
            return $this->{$name};
        }

        $locale = $locale ?? $this->getLocale();

        return $this->getTranslations($this->{$name}, $locale, $usingFallback);
    }
}
