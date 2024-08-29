<?php

namespace SolutionForest\InspireCms\Enums;

use Filament\Support\Contracts\HasLabel;

enum DefaultRoleEnums: string implements HasLabel
{
    case Admininistrator = 'admin';
    case Editor = 'editor';
    case Writer = 'writer';

    public function getLabel(): ?string
    {
        return match ($this) {
            static::Admininistrator => 'Admininistrators',
            static::Editor => 'Editors',
            static::Writer => 'Writers',
        };
    }
}
