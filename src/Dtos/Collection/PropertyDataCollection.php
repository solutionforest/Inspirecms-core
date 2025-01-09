<?php

namespace SolutionForest\InspireCms\Dtos\Collection;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Dtos\PropertyDataDto;

/**
 * @extends parent<PropertyDataDto>
 */
class PropertyDataCollection extends Collection
{
    public function __construct($items = [])
    {
        $items = collect($this->getArrayableItems($items))->mapWithKeys(function ($item, $key) {
            if ($item instanceof PropertyDataDto) {
                return [$item->key => $item];
            }

            return [$key => $item];
        });
        parent::__construct($items);
    }
}
