<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;
use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\UpdateContentRouteActionTrait;

class UpdateRouteAction extends Action
{
    use UpdateContentRouteActionTrait;

    public static function getDefaultName(): ?string
    {
        return 'update_route';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAction();
    }
}
