<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\ContentTree;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Livewire\Wireable;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\Filter\BaseFilter;

class FilterCollection extends Collection implements Wireable
{
    public function toLivewire()
    {
        return collect($this->items)
            ->flatMap(function ($item) {

                if ($item instanceof BaseFilter) {
                    return [$item->toLivewire()];
                }

                if (is_array($item) &&
                    Arr::first($item) instanceof BaseFilter
                ) {

                    return [Arr::first($item)->toLivewire()];

                }

                return [$item];
            })
            ->all();
    }

    public static function fromLivewire($value)
    {
        $items = collect(is_array($value) ? $value : [])
            ->map(function ($item) {

                if (! $item) {
                    return null;
                }

                if ($item instanceof BaseFilter) {
                    return $item;
                }

                $convertArrayToBaseFilter = function ($fqcn, $data) {
                    return $fqcn::fromLivewire($data);
                };

                if (is_array($item)) {
                    if (
                        ($firstItem = Arr::first($item)) &&
                        is_array($firstItem) &&
                        isset($firstItem['__fqcn']) &&
                        is_a($firstItem['__fqcn'], BaseFilter::class, true)
                    ) {
                        return $convertArrayToBaseFilter($firstItem['__fqcn'], $firstItem);

                    } elseif (
                        isset($item['__fqcn']) &&
                        is_a($item['__fqcn'], BaseFilter::class, true)
                    ) {
                        return $convertArrayToBaseFilter($item['__fqcn'], $item);
                    }

                    return $item;
                }

                return null;
            })
            ->filter()->values()
            ->all();

        return new static($items);
    }

    public function applyOnQuery(&$query)
    {
        foreach ($this->items as $item) {

            if ($item instanceof BaseFilter) {

                $query = $item->applyToQuery($query);

            } elseif (is_array($item)) {

                [$column, $operator, $value] = $item;

                if ($column instanceof BaseFilter) {
                    $query = $column->applyToQuery($query);

                    continue;
                }

                if ($column === 'id') {
                    if ($operator == 'not') {
                        $query->whereKeyNot($value);
                    } else {
                        $query->whereKey($value);
                    }
                } elseif ($operator == 'not') {
                    $query->whereNot($column, $value);
                } elseif ($operator == 'in') {
                    $query->whereIn($column, $value);
                } elseif ($operator == 'not in') {
                    $query->whereNotIn($column, $value);
                } else {
                    $query->where($column, $operator, $value);
                }
            }
        }
    }

    public function applyRecordFilter(Model $record)
    {
        foreach ($this->items as $item) {

            [$column, $operator, $value] = $item;

            if ($column instanceof BaseFilter) {
                return true;
            }

            if ($column === 'id') {
                if ($operator == 'not') {
                    return $record->getKey() != $value;
                } else {
                    return $record->getKey() == $value;
                }
            } elseif ($operator == 'not') {
                return $record->{$column} != $value;
            } elseif ($operator == 'in') {
                return in_array($record->{$column}, $value);
            } elseif ($operator == 'not in') {
                return ! in_array($record->{$column}, $value);
            } else {
                return $record->{$column} == ($value ?? $operator);
            }
        }
    }
}
