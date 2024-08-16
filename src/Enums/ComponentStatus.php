<?php

namespace SolutionForest\InspireCms\Enums;

use Filament\Support\Contracts\HasLabel;

enum ComponentStatus: string implements HasLabel
{
    case Draft = 'draft';
    case Published = 'published';
    
    public function getLabel(): ?string
    {
        return $this->name;
    }
}
