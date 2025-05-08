<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\InspireCms\Fields\PropertyValueTransformerInterface;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;
use SolutionForest\InspireCms\Support\Base\Dtos\Concerns\HasFallbackLocale;

/**
 * @extends BaseDto<PropertyDataDto>
 */
class PropertyDataDto extends BaseDto
{
    use HasFallbackLocale;

    /**
     * @var string
     */
    public $key;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @var ?PropertyTypeDto
     */
    public $propertyType;

    public function getValue(?string $locale = null): mixed
    {

        $locale ??= $this->getFallbackLocale();

        try {

            $transformer = app(PropertyValueTransformerInterface::class);

            return $transformer->attemptTransform($this, $locale, $this->getFallbackLocale());

        } catch (\Throwable $th) {
            return null;
        }
    }

    public function getSourceValue(): mixed
    {
        return $this->value;
    }
}
