<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Database\Eloquent\Model;

/**
 * @template TModle of Model
 */
abstract class BaseTranslatableModelDto extends BaseModelDto
{
    /**
     * @var ?string
     */
    protected $locale;

    /**
     * @var ?string
     */
    protected $fallbackLocale;

    protected array $translatableAttributes = [];

    /**
     * @param string
     * @return self
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string
     * @return self
     */
    public function setFallbackLocale(string $locale)
    {
        $this->fallbackLocale = $locale;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getFallbackLocale()
    {
        return $this->fallbackLocale;
    }
    
    /**
     * @return self
     */
    public static function fromArray(array $parameters)
    {
        return parent::fromArray($parameters);
    }

    /**
     * @param TModle $model
     * @param string
     * @return self
     */
    public static function fromModel($model)
    {
        return parent::fromModel($model);
    }

    /**
     * @param TModle $model
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

    protected function getTranslations($translations, ?string $locale = null, bool $usingFallback = true)
    {
        if (! $translations || ! is_array($translations)) {
            return $translations;
        }
        
        $locale = $locale ?? $this->getLocale();
        $fallbackLocale = $this->getFallbackLocale();

        $value = data_get($translations, $locale);

        if (! $value && $usingFallback && $locale !== $fallbackLocale && $fallbackLocale) {
            $value = $this->getTranslations($translations, $fallbackLocale, false);
        }

        return $value;
    }
}
