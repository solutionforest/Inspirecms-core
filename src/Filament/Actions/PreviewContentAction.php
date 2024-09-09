<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;
use Illuminate\Support\Js;

class PreviewContentAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'previewContent';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->alpineClickHandler("window.open(" . Js::from('/api/preview-content/2') . ",'winname','directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=350');");

        $this->groupedIcon('heroicon-o-eye');
        
        $this->icon('heroicon-o-eye');
    }
}
