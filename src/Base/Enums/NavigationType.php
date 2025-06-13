<?php

namespace SolutionForest\InspireCms\Base\Enums;

use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationType as InterfacesNavigationType;

enum NavigationType: string implements InterfacesNavigationType
{
    case Content = 'content';

    case Link = 'link';

    case Group = 'group';

    public function getLabel(): ?string
    {
        return match ($this) {
            default => $this->name,
        };
    }

    public static function getDefaultValue(): InterfacesNavigationType
    {
        return self::Group;
    }

    public function canEditIsVisible(): bool
    {
        return $this->value !== self::Content->value;
    }
}
