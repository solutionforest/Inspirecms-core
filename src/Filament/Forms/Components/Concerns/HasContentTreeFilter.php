<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\Concerns;

trait HasContentTreeFilter
{
    public array $filters = [];

    public function filter(array $filters, bool $merge = true): static
    {
        $this->filters = $merge ? array_merge($this->filters, $filters) : $filters;

        return $this;
    }

    public function whereKey($key): static
    {
        return $this->where('id', '==', $key);
    }

    public function whereKeyNot($key): static
    {
        return $this->whereNot('id', $key);
    }

    public function whereIn($key, $values): static
    {
        return $this->where($key, 'in', $values);
    }

    public function whereNotIn($key, $values): static
    {
        return $this->where($key, 'not in', $values);
    }

    public function whereNot($key, $value): static
    {
        return $this->where($key, 'not', $value);
    }

    public function where($key, $operator = null, $value = null): static
    {
        return $this->filter([
            [
                $key,
                $operator,
                $value,
            ]
        ], true);
    }

    public function getFilters(): array
    {
        return $this->filters;
    }
}
