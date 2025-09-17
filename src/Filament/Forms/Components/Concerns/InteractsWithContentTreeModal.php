<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\Concerns;

use Filament\Forms\Components\Concerns\CanLimitItemsLength;

trait InteractsWithContentTreeModal
{
    use CanLimitItemsLength;
    use WithContentTreeNode;

    public function getContentTreeModalConfig(): array
    {
        return [
            'limits' => [
                'min' => $this->getMinItems(),
                'max' => $this->getMaxItems(),
            ],
            'filter' => $this->getFilter()->toLivewire(),
            'filteringByPermission' => $this->isFilteringByPermission(),
            'startNode' => $this->getStartNode(),
        ];
    }

    public function getContentTreeModalId(): string
    {
        return 'content-tree-picker-modal';
    }
}
