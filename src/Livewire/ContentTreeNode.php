<?php

namespace SolutionForest\InspireCms\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\WithPagination;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\FilterCollection;

#[Lazy]
class ContentTreeNode extends BaseContentTreeNode
{
    use WithPagination;

    protected static bool $showNavigationHeader = false;

    protected static bool $skipChildrenIfTableView = false;

    public ?string $search = null;

    public bool $isDisabled = true;

    public ?FilterCollection $filter = null;

    #[Locked]
    public $isPickerInModal = false;

    public array $modelableConfig = [];

    public int $paginationPageSize = 25;

    #[Modelable]
    public array $selectedNodes = [];

    protected static array $paginationOptions = [10, 25, 50, 100, 200, 'all'];

    public function isFilteringBySearch(): bool
    {
        return filled($this->search);
    }

    public function updatedSearch()
    {
        $this->refreshTree();
    }

    /**
     * Get paginated search results when search is active
     */
    protected function getSearchRecords()
    {
        if (!$this->isFilteringBySearch()) {
            return null;
        }

        $query = $this->getElquentQuery();
        
        // Apply search filter to the query
        $query->where(function (Builder $q) {
            $searchTerm = '%' . $this->search . '%';
            $q->where('title', 'like', $searchTerm)
              ->orWhere('slug', 'like', $searchTerm);
        });

        return $query->paginate($this->paginationPageSize);
    }

    protected function getElquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getElquentQuery();

        if ($this->filter && $this->filter instanceof FilterCollection) {
            $this->filter->applyOnQuery($query);
        }

        return $query;
    }

    protected function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'searchRecords' => $this->getSearchRecords(),
            'pageOptions' => static::$paginationOptions,
        ]);
    }

    public function placeholder()
    {
        return <<<'HTML'
            <div>Loading...</div>
        HTML;
    }

    public function render()
    {
        return view('inspirecms::livewire.content-tree-node', $this->viewData());
    }
}
