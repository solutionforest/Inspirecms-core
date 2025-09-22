<?php

namespace SolutionForest\InspireCms\Livewire;

use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\FilterCollection;

class ContentTreeNode extends BaseContentTreeNode
{
    protected static bool $showNavigationHeader = false;
    protected static bool $enableSelection = true;

    public ?string $search = null;

    public array $limits = [];

    public bool $isDisabled = true;

    public ?FilterCollection $filter = null;

    #[Locked]
    public $isModalPicker = true;

    // public array $modalConfig = [];

    public array $modelableConfig = [];


    // #[On('content-tree-node:modal-setup')]
    // #[Renderless] // prevent re-rendering on event
    // public function setUpModalConfig($key = null, $selected = [], $config = []): void
    // {
    //     if (!$this->isContentPickerModal()) {
    //         return;
    //     }
    //     $this->modalConfig = $config;
    //     if (!empty($config)) {
    //         foreach ($config as $k => $v) {
    //             switch ($k) {
    //                 case 'startNode':
    //                     $this->startNode = $v;
    //                     break;
    //                 case 'limits':
    //                     $this->limits = is_array($v) ? $v : [];
    //                     break;
    //                 case 'filter':
    //                     if ($v instanceof FilterCollection) {
    //                         $this->filter = $v;
    //                     } elseif (is_array($v)) {
    //                         $this->filter = FilterCollection::fromLivewire($v);
    //                     } else {
    //                         $this->filter = null;
    //                     }
    //                     break;
    //                 case 'filterByPermission':
    //                     if (is_string($v) && ! empty($v)) {
    //                         $this->filter = new FilterCollection([
    //                             ['id', 'in', auth()->user()->getAllPermissions()->pluck('id')->all()],
    //                         ]);
    //                     } else {
    //                         $this->filter = null;
    //                     }
    //                     break;
    //             }
    //         }

    //     }
    // }

    public function isContentPickerModal(): bool
    {
        return $this->isModalPicker;
    }

    protected function getExtraAlpineAttributes(): array
    {
        $attributes = [];

        if (filled($this->modelableConfig) && is_array($this->modelableConfig)) {
            $key = array_key_first($this->modelableConfig);
            $value = Arr::first($this->modelableConfig);
            if (is_string($key) && !empty($key) && is_string($value) && !empty($value)) {
                $attributes['x-modelable'] = $key;
                $attributes['x-model'] = "state";
            }
        }

        return $attributes;
    }

    public function getExtraAlpineAttributeBag()
    {
        return new \Illuminate\View\ComponentAttributeBag($this->getExtraAlpineAttributes());
    }

    public function isFilteringBySearch(): bool
    {
        return filled($this->search);
    }

    public function updatedSearch()
    {
        $this->refreshTree();
    }

    protected function getElquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getElquentQuery();

        // if ($this->isFilteringBySearch()) {
        //     $query->where(function ($q) {
        //         $q->where('title', 'like', '%'.$this->search.'%')
        //             ->orWhere('slug', 'like', '%'.$this->search.'%');
        //     });
        // }

        if ($this->filter instanceof FilterCollection) {
            $this->filter->applyOnQuery($query);
        }

        return $query;
    }

    public function render()
    {
        return view('inspirecms::livewire.content-tree-node', $this->viewData());
    }
}
