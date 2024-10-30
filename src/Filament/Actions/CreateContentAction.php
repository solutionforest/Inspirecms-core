<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;
use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\CreateContentActionTrait;

class CreateContentAction extends Action
{
    use CreateContentActionTrait;

    public static function getDefaultName(): ?string
    {
        return 'create_content';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAction(Action::class);
    }
}
