<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\Actions;

use Filament\Forms\Components\Actions\Action;

class SelectAction extends Action
{
    use \Filament\Actions\Concerns\HasSelect;

    protected function setUp(): void
    {
        parent::setUp();

        $this->view('filament-actions::select-action');
    }
}
