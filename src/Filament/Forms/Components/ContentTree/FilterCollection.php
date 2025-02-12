<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\ContentTree;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Wireable;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\Filter\BaseFilter;

class FilterCollection extends Collection implements Wireable
{
    public function toLivewire()
    {
        return $this->items;
    }

    public static function fromLivewire($value)
    {
        return new static($value);
    }

    public function applyOnQuery(&$query)
    {
        foreach ($this->items as $item) {

            [$column, $operator, $value] = $item;

            if ($column instanceof BaseFilter) {
                $query = $column->applyToQuery($query);

                return;
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

    public function applyRecordFilter(Model $record): bool
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
