<?php

namespace SolutionForest\InspireCms\Filament\TreeNode\Actions;

use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\CanCustomizeAuthorizedGuardActionProcess;
use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\ReorderContentActionTrait;
use SolutionForest\InspireCms\Filament\Contracts\GuardAction;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action;

class ReorderContentItemAction extends Action implements GuardAction
{
    use CanCustomizeAuthorizedGuardActionProcess;
    use ReorderContentActionTrait;

    public static function getDefaultName(): ?string
    {
        return 'reorder_content_item';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAction();
    }
}
