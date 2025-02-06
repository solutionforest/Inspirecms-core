<?php

namespace SolutionForest\InspireCms\Base\Filament\Actions\Concerns;

use Filament\Actions\Action;

trait WithPagination
{
    use \Livewire\WithPagination;

    public array $pageOptions = [];

    public null | string | int $perPage = null;

    protected function getPaginationActions(): array
    {
        return [
            Action::make('gotoPage')
                ->action(fn (array $arguments) => $this->setPage($arguments['page'] ?? null)),
            Action::make('previousPage')
                ->action(fn (array $arguments) => $this->previousPage()),
            Action::make('nextPage')
                ->action(fn (array $arguments) => $this->nextPage()),

            Action::make('changePageOption')
                ->action(function (array $arguments) {
                    if (isset($arguments['value'])) {
                        $this->setPerPage($arguments['value']);
                    }
                }),
        ];
    }

    public function setPageOptions(array $options)
    {
        $this->pageOptions = $options;

        return $this;
    }

    public function getPageOptions(): array
    {
        return $this->pageOptions;
    }

    public function setPerPage(null | string | int $perPage)
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function getPerPage(): null | string | int
    {
        return $this->perPage;
    }
}
