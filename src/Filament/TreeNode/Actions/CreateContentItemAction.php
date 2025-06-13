<?php

namespace SolutionForest\InspireCms\Filament\TreeNode\Actions;

use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\CreateContentActionTrait;
use SolutionForest\InspireCms\Support\TreeNode\Actions\Action;

class CreateContentItemAction extends Action
{
    use CreateContentActionTrait;

    public static function getDefaultName(): ?string
    {
        return 'createContentItem';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAction();
    }
}
