<?php

namespace SolutionForest\InspireCms\Filament\TreeNode\Actions;

use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\ReorderContentActionTrait;
use SolutionForest\InspireCms\Support\TreeNode\Actions\Action;

class ReorderContentItemAction extends Action
{
    use ReorderContentActionTrait;

    public static function getDefaultName(): ?string
    {
        return 'reorderContentChildrenItem';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAction();
    }
}
