<?php

namespace SolutionForest\InspireCms\Base\Dtos\Concerns;

use SolutionForest\InspireCms\Dtos\Collection\PropertyGroupCollection;
use SolutionForest\InspireCms\Dtos\PropertyDataGroupDto;

trait HasPropertyGroup
{
    /**
     * @var PropertyGroupCollection
     */
    public $propertyData;

    /**
     * Retrieves a property group by its key.
     *
     * @param  string  $key  The unique identifier for the property group
     * @return ?PropertyDataGroupDto
     */
    public function getPropertyGroup(string $key)
    {
        if (! $this->propertyData instanceof PropertyGroupCollection) {
            $this->propertyData = PropertyGroupCollection::make($this->propertyData);
        }

        $result = $this->propertyData->get($key, null);
        
        if ($result) {
            
            $locale = $this->getLocale() ?? $this->getFallbackLocale();

            if ($result instanceof PropertyGroupCollection
                || $result instanceof PropertyDataGroupDto
            ) {
                $result = $result->setFallbackLocale($locale);
            }
        }

        return $result;
    }

    public function hasPropertyGroup(string $key): bool
    {
        return $this->propertyData->has($key);
    }
}
