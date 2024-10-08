<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\Concerns;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator as ContractsPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

trait HasPaginationOptions
{
    protected null | Closure | Builder $paginationOptions = null;

    protected int | string $perPage = 10;

    public function paginationOptions(Closure | Builder $options): static
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

        $paginationOptions = $this->evaluate($this->paginationOptions);

        if (! $paginationOptions) {
            return new Paginator([], $this->perPage, options: ['pageName' => $pageName]);
        }

        return $paginationOptions->paginate($this->perPage, pageName: $pageName);
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
