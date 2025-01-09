<?php

namespace SolutionForest\InspireCms\Dtos\Collection;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Dtos\PropertyDataDto;
use SolutionForest\InspireCms\Dtos\PropertyDataGroupDto;
use SolutionForest\InspireCms\Support\Base\Dtos\Concerns\HasFallbackLocale;

/**
 * @extends parent<PropertyDataGroupDto>
 */
class PropertyGroupCollection extends Collection
{
    use HasFallbackLocale;

    public function __construct($items = [])
    {
        $items = collect($this->getArrayableItems($items))->mapWithKeys(function ($item, $key) {
            if ($item instanceof PropertyDataGroupDto) {
                return [$item->key => $item];
            }

            return [$key => $item];
        });
        parent::__construct($items);
    }

    public function get($key, $default = null)
    {
        $result = parent::get($key, $default);

        if (! is_null($result) && $result instanceof PropertyDataGroupDto && ($locale = $this->getFallbackLocale()) != null) {
            $result->setFallbackLocale($locale);
        }

        return $result;
    }

    /**
     * Retrieve property data by key.
     *
     * @param  string  $key  The key of the property to retrieve.
     * @return Collection<string,PropertyDataDto> The property data associated with the group key.
     */
    public function getPropertyData(string $key)
    {
        return $this
            ->whereInstanceOf(PropertyDataGroupDto::class)
            ->mapWithKeys(fn (PropertyDataGroupDto $group) => [
                $group->key => $group->data->get($key),
            ])
            ->filter()
            ->map(
                fn (PropertyDataDto $d) => ($locale = $this->getFallbackLocale()) != null
                ? $d->setFallbackLocale($locale)
                : $d
            )
            ->toBase();
    }
}
