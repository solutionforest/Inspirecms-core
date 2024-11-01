<?php

namespace SolutionForest\InspireCms\Filament\TreeNode\Actions;

use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\CreateContentActionTrait;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action;

class CreateContentItemAction extends Action
{
    use CreateContentActionTrait;

    public static function getDefaultName(): ?string
    {
        return 'create_content_item';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAction();
    }
}
