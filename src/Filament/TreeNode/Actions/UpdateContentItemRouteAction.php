<?php

namespace SolutionForest\InspireCms\Filament\TreeNode\Actions;

use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\UpdateContentRouteActionTrait;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action;

class UpdateContentItemRouteAction extends Action
{
    use UpdateContentRouteActionTrait;

    public static function getDefaultName(): ?string
    {
        return 'update_content_item_route';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAction();
    }
}
