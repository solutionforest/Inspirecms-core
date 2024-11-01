<?php

namespace SolutionForest\InspireCms\Filament\TreeNode\Actions;

use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\CanCustomizeAuthorizedGuardActionProcess;
use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\LinkContentToParentActionTrait;
use SolutionForest\InspireCms\Filament\Contracts\GuardAction;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action;

class LinkContentItemToParentAction extends Action implements GuardAction
{
    use CanCustomizeAuthorizedGuardActionProcess;
    use LinkContentToParentActionTrait;

    public static function getDefaultName(): ?string
    {
        return 'link_content_item_to_parent';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAction();
    }
}
