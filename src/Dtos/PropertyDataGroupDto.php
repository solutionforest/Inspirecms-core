<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Dtos\Collection\PropertyDataCollection;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;
use SolutionForest\InspireCms\Support\Base\Dtos\Concerns\HasFallbackLocale;

/**
 * @extends BaseDto<PropertyDataGroupDto>
 */
class PropertyDataGroupDto extends BaseDto
{
    use HasFallbackLocale;
    
    /**
     * @var string
     */
    public $key;

    /**
     * @var PropertyDataCollection
     */
    public $data;

    /**
     * @var Collection<PropertyTypeDto>
     */
    public $propertyTypes;

    public static function fromArray(array $parameters)
    {
        $dto = parent::fromArray($parameters);

        // Ensure data is a collection
        $dto->data = new PropertyDataCollection($dto->data ?? []);
        $dto->propertyTypes = collect($dto->propertyTypes ?? []);

        return $dto;
    }

    /**
     * @return ?PropertyDataDto
     */
    public function getPropertyData(string $key, ?string $locale = null)
    {
        /**
         * @var ?PropertyDataDto
         */
        $data = collect($this->data)->first(fn (PropertyDataDto $p) => $p->key == $key);

        if (is_null($data)) {
            return null;
        }

        $locale ??= $this->getFallbackLocale();
        if ($locale) {
            $data->setFallbackLocale($locale);
        }
        
        return $data;
    }
}
