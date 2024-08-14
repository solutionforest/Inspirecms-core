<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;

class PageHistoryAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'pageHistory';
    }

    protected function setUp(): void
    {
        parent::setUp();

    }
}
