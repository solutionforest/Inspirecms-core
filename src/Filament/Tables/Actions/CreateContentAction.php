<?php

namespace SolutionForest\InspireCms\Filament\Tables\Actions;

use Filament\Actions\Action;
use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\CreateContentActionTrait;

class CreateContentAction extends Action
{
    use CreateContentActionTrait;

    protected static string $actionType = Action::class;

    public static function getDefaultName(): ?string
    {
        return 'createContent';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAction();
    }
}
