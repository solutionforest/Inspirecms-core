<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator as ContractsPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

trait HasPaginationOptions
{
    protected ?Builder $paginationOptions = null;

    protected int | string $perPage = 10;

    public function paginationOptions(Builder $options): static
    {
        $this->paginationOptions = $options;

        return $this;
    }

    public function perPage(int | string $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function getPaginationOptions(): LengthAwarePaginator | ContractsPaginator
    {
        $pageName = $this->getPaginationName();

        if (! $this->paginationOptions) {
            return new Paginator([], $this->perPage, options: ['pageName' => $pageName]);
        }

        return $this->paginationOptions->paginate($this->perPage, pageName: $pageName);
    }

    public function getPerPage(): int | string
    {
        return $this->perPage;
    }

    public function getPaginationName(): string
    {
        return 'mountFormComponentPagination_' . $this->getName();
    }
}
