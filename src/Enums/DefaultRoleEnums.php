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
            self::Admininistrator => 'Admininistrators',
            self::Editor => 'Editors',
            self::Writer => 'Writers',
        };
    }
}
