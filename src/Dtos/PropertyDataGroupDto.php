<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

/**
 * @extends BaseDto<PropertyDataGroupDto>
 */
class PropertyDataGroupDto extends BaseDto
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var Collection<PropertyDataDto>
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
        $dto->data = collect($dto->data ?? []);
        $dto->propertyTypes = collect($dto->propertyTypes ?? []);

        return $dto;
    }

    /**
     * @return ?PropertyDataDto
     */
    public function getPropertyData(string $key)
    {
        return collect($this->data)->first(fn (PropertyDataDto $p) => $p->key === $key);
    }
}
