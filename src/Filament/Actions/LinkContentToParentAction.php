<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;
use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\CanCustomizeAuthorizedGuardActionProcess;
use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\LinkContentToParentActionTrait;
use SolutionForest\InspireCms\Filament\Contracts\GuardAction;

class LinkContentToParentAction extends Action implements GuardAction
{
    use CanCustomizeAuthorizedGuardActionProcess;
    use LinkContentToParentActionTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAction();
    }
}
