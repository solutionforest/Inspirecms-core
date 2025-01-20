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

    public int $currentPage = 1;

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

    protected function getPaginationOptionsQuery(): ?Builder
    {
        return $this->evaluate($this->paginationOptions);
    }

    public function getPaginationOptions(): LengthAwarePaginator | ContractsPaginator
    {
        $paginationOptions = $this->getPaginationOptionsQuery();

        if (! $paginationOptions) {
            return new Paginator(
                items: [], 
                perPage: $this->perPage, 
                currentPage: $this->currentPage
            );
        }

        return $paginationOptions->paginate(
            perPage:$this->perPage, 
            page: $this->currentPage,
        );
    }

    public function getPerPage(): int | string
    {
        return $this->perPage;
    }
}
