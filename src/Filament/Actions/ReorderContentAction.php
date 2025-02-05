<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;
use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\ReorderContentActionTrait;

class ReorderContentAction extends Action
{
    use ReorderContentActionTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAction();
    }
}
